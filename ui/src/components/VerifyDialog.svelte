<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import Icon from "./Icons.svelte";
  import { apiCheck, db } from "../ConfigService.js";

  export let visible = false;
  let data;
  export let url;
  export let type;
  export let needsUpdate;
  let getFromObject=false;
  
  const titles = {url:"Adresa", dead:"Mrtvý", deadIndex:"Index úmrtí", manualCheck:"Vyžadována ruční kontrola", code:"Poslední stavový kód", redirect:"Počet přesměrování", redirectToAnotherDomain:"Přesměrování na jinou doménu", pagedata_diff:"Rozdílná metadata", pagedata_count:"Počet metadat", pagedata:"Aktuální metadata", whois_diff:"Rozdílné Whois", whois_count:"Počet Whois", whois:"Aktuální whois"};
 
  async function update() {
	    if(url && (type=="verify")) {
		const res = await fetch($apiCheck+"/?url="+url+"?db="+$db);
		data = await res.json();
		visible=true;
	    }
  }

 $: {
 	update(url, type);
 	if(!visible) { type=false; needsUpdate=true; }
 }
 
 function close() {
 	url=false;
 	data=false;
 	visible=false;
 	type=false;
 	needsUpdate=true;
 }

 function isNotEmpty(value) {
 	if(!value) { return false; }
 	if(value=="null") { return false; }
 	return true;
 }
 
 function translate(k) {
 	for (const [key, value] of Object.entries(titles)) {
		if(key==k) { return value; }
	}
 	return k;
 }
 
 function isBoolean(value) {
 	if (typeof value === 'boolean') { return true; }
 	return false;
 }

 function objectIntoLines(obj) {
 	let out="";
 	if(typeof obj !== "object") { return obj; }
	for(const [key, value] of Object.entries(obj)) {
		if(typeof value !== "object") { out+=value+"\n"; }
		else {
			let first=true;
			for(const [key2, value2] of Object.entries(value)) {
				if(first) { first=false; } else { out+=", "; }
				out+=value2.replaceAll(",", ", ");
			}
		}
	}
 	return out;
 }

</script>

<Dialog width="85%" bind:visible style="overflow-x: auto !important; min-width:400px;">
    <div slot="title">Ověření dostupnosti webu</div>
{#await data}
	<Loading />
{:then data} 
	{#if data}				
		{#each Object.entries(data) as [key, value]}
			<span class="key">{translate(key)}: </span> 
			{#if typeof(value) === 'object'}
				{#each Object.entries(value) as [key2, value2]}
					{#if isNotEmpty(value2)}
						<div class="subcategory	">
						<span class="key">{translate(key2)}:</span>
						{#if typeof(value2) === 'object'}
							<br>
							{#each Object.entries(value2) as [key3, value3]}
								{#if isNotEmpty(value3)}
									<span class="key">&nbsp;&nbsp;&nbsp;&#x25B6;&nbsp;{translate(key3)}:</span> 
									{objectIntoLines(value3)}<br>
								{/if}
							{/each}
						{:else}
							{value2}
						{/if}
						</div>
					{/if}
				{/each}
			{:else if isBoolean(value) && value}
				<Icon type="ok" />
			{:else if isBoolean(value) && !value}
				<Icon type="no" />
			{:else}
				{value}
			{/if}			
			<br>	
		{/each}
	{:else}
		<Loading />
	{/if}
{/await}
    <div slot="actions" class="actions center">
        <Button outlined on:click="{()=>(close())}">Zavřít</Button>
    </div>
</Dialog>


<style>
a { cursor:pointer; }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
.tableValues td { vertical-align:top; border:1px dashed silver; padding:5px; max-width:200px; }
</style>
