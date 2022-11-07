<script>
 	import { solr, oaiLoadCount } from "../ConfigService.js";
	import Loading from "./Loading.svelte";
	export let uuid;
	export let oldUuid;
	let rootPid="-";
	let pidPath="-";
	
	export let id=0;
	export let idNumberFinished=0;
	
	let data = (async () => {
	    const response = await fetch($solr+'fl=PID,pid_path&indent=on&q=parent_pid:%22'+uuid+'%22,%20(fedora.model:%20%22page%22 OR fedora.model:%20%22periodicalitem%22)&rows=1&wt=json');
	    oldUuid=uuid;
	    return await response.json()
	})()
	
	 async function loadCountRootPid() {
	      const res = await fetch($solr+'fl=PID,pid_path&indent=on&q=root_pid:%22'+uuid+'%22,%20(fedora.model:%20%22page%22 OR fedora.model:%20%22periodicalitem%22)&rows=1&wt=json');
	      let data2 = await res.json();
	      if(Number.isInteger(await data2.response.numFound)) { rootPid=await data2.response.numFound; }
	      return rootPid;
	  }
	  
	  async function loadCountPidPath(docs) {
	  	let pp=0; 
	  	if(docs && docs.length>0) { 
	  		if(docs[0].pid_path) { pp=docs[0].pid_path; }
	  	}
	  	if(pp!=0) {
			const res = await fetch($solr+'fl=PID&indent=on&q=pid_path:%22'+pp+'%22,%20(fedora.model:%20%22page%22),&rows=1&wt=json');
			data = await res.json();
			if(Number.isInteger(await data.response.numFound)) { pidPath=(await (data.response.numFound)-2); }
			if(pidPath<1) { pidPath="-"; }
		}
		return pidPath;
	  }
	
	function finishedNumber() {
		if($oaiLoadCount) { idNumberFinished+=$oaiLoadCount; }
		else { idNumberFinished++; }
		return "";
	}
	
	async function updateData() {
		const response = await fetch($solr+'fl=PID&indent=on&q=parent_pid:%22'+uuid+'%22,%20(fedora.model:%20%22page%22 OR fedora.model:%20%22periodicalitem%22)&rows=1&wt=json');
		oldUuid=uuid;
		data=await response.json();
	}
	
	$: {
		if(oldUuid && oldUuid!=uuid) { updateData(); }
	}
</script>

{#if id>(idNumberFinished+1)}
	<Loading small=true />
{:else}
	{#await data}
		<Loading />
	{:then data} 
		{#if data.response.numFound && data.response.numFound>1}
			{data.response.numFound}
		{:else if loadCountRootPid() && rootPid!=0}
			{rootPid}
		{:else if data.response.docs && loadCountPidPath(data.response.docs)}
			{pidPath}
		{:else}
			?
		{/if}
		{finishedNumber()}
	{:catch error}
		-
	{/await}
{/if}


<style>
	
</style>
