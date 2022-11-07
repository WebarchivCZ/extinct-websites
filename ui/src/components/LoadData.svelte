<script>
 import { solr } from "../ConfigService.js";
 import Loading from "../components/Loading.svelte";
 import Pagination from "../components/Pagination.svelte";
 import Table from "../components/Table.svelte";
 import LimitSelect from "../components/LimitSelect.svelte";
 import Actions from "../components/Actions.svelte";
 
 export let limit=100;
 export let numberOfPages=false;
 export let oai;
 export let pdf=false;
 export let login;
 export let name;
 
 export let query="indent=on&q=dnnt:false,%20-fedora.model:%22page%22";
 export let rows=["root_title", "dc.title", "PID", "rok", "datum_begin", "datum_end", "fedora.model", "dostupnost"];
 export let rowsTitle=["Název", "číslo","UUID", "rok", "od", "do", "typ", "veřejné"];
 export let checkbox=[];
 export let licences=[];
 export let sddntLicence=[];
 let selAll=false;
 export let filteredByType=false;

 export let page=1;
 export let lastPage=1;
 let showLoading=false;
 
 export let issue;
 let fq="";
 
 function getRows() {
 	let fl="";
 	for(let i=0; i<rows.length; i++) {
 		if(fl!="") { fl+=","; }
 		fl+=rows[i];
 	}
 	return "fl="+fl+"&";
 }

let data=false;
/*
let data = (async () => {
    fq="&fq=-fedora.model:%22page%22";
    if(!issue) { fq+=" AND -fedora.model:%22periodicalitem%22 AND -fedora.model:%22supplement%22"; }
    const response = await fetch($solr+getRows()+query+fq+'&rows='+limit+'&start='+((page-1)*limit)+'&wt=json');
    lastpage=page;
    return await response.json()
})()*/

  async function update() {
      showLoading=true; 
      fq="&fq=-fedora.model:%22page%22";
      if(issue) {
      	      if(filteredByType=="refresh") { page=lastPage; filteredByType=false; }
	      if(issue.id=="volume" || (filteredByType && issue.id!="item")) { fq+=" AND -fedora.model:%22periodicalitem%22 AND -fedora.model:%22supplement%22 AND -fedora.model:%22internalpart%22"; }
	      else if(issue.id=="item" || filteredByType) { fq+=""; }
	      else { fq+=" AND -fedora.model:%22periodicalvolume%22 AND -fedora.model:%22monographunit%22 AND -fedora.model:%22periodicalitem%22 AND -fedora.model:%22supplement%22 AND -fedora.model:%22internalpart%22"; }
	      if(filteredByType) {
	      		if(name) {
			      	query="sort=level ASC&sort=dc.title ASC&"+query; 
			      	lastPage=page; 
			      	page=1; 
			} 
	      }
      }
      const res = await fetch($solr+getRows()+query+fq+'&rows='+limit+'&start='+((page-1)*limit)+'&wt=json');
      if(lastPage!=page && !filteredByType) {
      		checkbox=[];
      		lastPage=page;    
      }
      data = await res.json();
      if(await res) { showLoading=false; }
  }

  $: {
    if(issue) { update(limit, page, query, issue); }
  }

</script>

{#if showLoading}
	<Loading />
{:else}
{#await data}
	<Loading />
{:then data} 
 {#if data.response.numFound} 
  <LimitSelect bind:limit />
  <Pagination bind:page bind:limit countDocs={data.response.numFound} bind:filteredByType />
  
  <Table data={data.response.docs} bind:oai bind:numberOfPages bind:page bind:lastPage bind:rows bind:rowsTitle bind:checkbox bind:licences bind:pdf bind:name bind:filteredByType bind:sddntLicence linkToIssues="true" />
  <br>
  <Pagination bind:page bind:limit countDocs={data.response.numFound} bind:filteredByType />
 
  <br><br>
  <Actions docs={data.response.docs} bind:oai bind:rows bind:checkbox bind:licences bind:sddntLicence bind:pdf bind:login />
  
  <br><center><a href="#table">nahoru</a></center><br><br>
 {:else}
 	<p>Žádné výsledky!</p>
 {/if}
{:catch error}
	<p>Chyba načítání!</p>
{/await}
{/if}

