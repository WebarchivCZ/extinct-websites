<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import { api, db } from "../ConfigService.js";

  export let visible = false;
  let data;
  export let uuid;
  export let type;
  let getFromObject=false;
 
  async function update() {
	    if(uuid && type) {
	    	if(type=="page") { getFromObject="page_data"; }
	    	else if(type=="whois") { getFromObject="whois"; }
	    	else { getFromObject="harvest_metadata"; }
		const res = await fetch($api+"?db="+$db+"&uuid="+uuid);	//+"&type="+type) 
		data = await res.json();
	    }
  }

 $: {
	if(uuid && (type=="harvest" || type=="page" || type=="whois")) { 
		visible=true; 
		update(uuid, type);
	}
 }
 
 function close() {
 	uuid=false;
 	type=false;
 	data=false;
 	visible=false;
 }
 
 function isNotEmpty(value) {
 	if(!value) { return false; }
 	if(value=="null") { return false; }
 	return true;
 }
 
</script>

<Dialog width="700" bind:visible>
    <div slot="title">Zobrazení dat</div>
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
					{#each Object.entries(value) as [key2, value2]}
						{#if isNotEmpty(value2) && key2!="id"}
							<span class="key">{key2}:</span> {value2}<br>
						{/if}
					{/each}
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
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
</style>
