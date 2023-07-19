<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import Icon from "../components/Icons.svelte";
  import { api, db } from "../ConfigService.js";

  export let visible = false;
  let data;
  export let uuid;
  export let url;
  export let type;
  let getFromObject=false;
 
  async function update() {
	    if(uuid && (type=="harvest" || type=="page" || type=="whois")) {
	    	if(type=="page") { getFromObject="page_data"; }
	    	else if(type=="whois") { getFromObject="whois"; }
	    	else { getFromObject="harvest_metadata"; }
		const res = await fetch($api+"?db="+$db+"&uuid="+uuid);	//+"&type="+type) 
		data = await res.json();
		visible=true;
	    }
  }

 $: {
 	update(uuid, type);
 	if(!visible) { type=false; }
 	/*
	if(uuid && (type=="harvest" || type=="page" || type=="whois")) { 
		visible=true; 
		update(uuid, type);
	}*/
 }
 
 function close() {
 	type=false;
 	uuid=false;
 	data=false;
 	visible=false;
 }
 
 function changeType(t) {
 	visible=false;
 	type=t;
 }
 
 function isNotEmpty(value) {
 	if(!value) { return false; }
 	if(value=="null") { return false; }
 	return true;
 }
 
 function objectIntoLines(obj) {
 	let out="";
	for(const [key, value] of Object.entries(obj)) {
	  out+=value.replaceAll(",", ", ")+"<br>";
	}
 	return out;
 }
 
</script>

<Dialog width="85%" bind:visible style="overflow-x: auto !important; min-width:400px;" beforeClose={()=>close()}>
    <div slot="title">
    	Zobrazení dat<br>
    	<small>{url}</small>
    	<div class="float-right">
  		{#if type=="harvest"}
  			<span title="Harvest data" class="activeIcon"><Icon type="info" /></span>
  		{:else}
  			<span title="Harvest data" on:click="{()=>(changeType('harvest'))}"><Icon type="info" /></span>
  		{/if}
	  	{#if type=="page"}
  			<span title="Page data" class="activeIcon"><Icon type="page" /></span>
  		{:else}
  			<span title="Page data" on:click="{()=>(changeType('page'))}"><Icon type="page" /></span>
  		{/if}
  		{#if type=="whois"}
  			<span title="Whois" class="activeIcon"><Icon type="contact" /></span>
  		{:else}
  			<span title="Whois" on:click="{()=>(changeType('whois'))}"><Icon type="contact" /></span>
  		{/if}
	</div>
    </div>
{#await data}
	<Loading />
{:then data} 
	{#if data}
	  {#if data.data[0]}
	    {#if data.data[0][getFromObject]}
		{#if !data.data[0][getFromObject][0]}
			{#each Object.entries(data.data[0][getFromObject]) as [key, value]}
				<span class="key">&#9660; {key}</span>
				<div class="subcategory">
					<table class="tableValues">
					<tr>
					{#each Object.entries(value) as [key2, value2]}
						{#if key2!="id"}
							<td class="key" style="text-align:center;">{key2}</td>
						{/if}
					{/each}
					</tr>
					<tr>
					{#each Object.entries(value) as [key2, value2]}
						{#if isNotEmpty(value2) && key2!="id"}
							<td>{@html objectIntoLines(value2)}</td>
						{/if}
					{/each}
					</tr>
					</table>
				</div>
			{/each}
		{:else}
			{#each data.data[0][getFromObject] as obj}
				{#if typeof(obj) === 'object'}
					{#each Object.entries(obj) as [key, value]}
						{#if typeof(value) === 'object'}
							<span class="key">&#9660; {key}</span>
							<div class="subcategory">
								{#each value as v}
									{#each Object.entries(v) as [key2, value2]}
										{#if isNotEmpty(value2) && key2!="id"}
											<span class="key">{key2}:</span> {value2}<br>
										{/if}
									{/each}
								{/each}
							</div>
						{:else if isNotEmpty(value) && key!="id"} 
							<span class="key">{key}: </span> {value}<br>
						{/if}
					{/each}
				{/if}
				<hr />
			{/each}
		{/if}
	   {:else}
	   	Data nejsou sklizena :(
	   {/if}
	 {:else}
	 	Data nejsou sklizena :(
	 {/if}
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
.activeIcon { color:rgb(98, 0, 238); }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
.tableValues td { vertical-align:top; border:1px dashed silver; padding:5px; max-width:200px; }
</style>
