<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import { apiFeeder, db } from "../ConfigService.js";

  export let visible = false;
  export let type;
  export let data;
  
  async function getData() {
  	const response = await fetch($apiFeeder);
	data = await response.json();
  }

 $: {
	if(type=="recordCheck") { visible=true; getData(); }
 }
 
 function close() {
 	visible=false;
 	type=false;
 }
 
 function getUrlName(url) {
 	return url.replaceAll("https://", "").replaceAll("http://", "");
 }
 
</script>

<Dialog width="600" bind:visible beforeClose={()=>close()}>
    <div slot="title">Fronta kontrolovaných URL</div>
   {#await data}
   
   {:then data}
   	{#if data}
   	  {#if data.sum.total>0}
   		<b>Ve frontě: {data.sum.total} adres</b>
   		<br /><br />
   		<table>
   		<tr><th>#</th><th>URL</th></tr>
   		{#each data.data as url,i}
   			<tr>
   				<td>{i+1}</td>
   				<td><a href="{url}" target="_blank">{getUrlName(url)}</a></td>
   			</tr>
   		{/each}
   		</table>
   	  {:else}
   		<b>Žádné URL ve frontě :)</b>
   	  {/if}
   	{/if}
   {/await}
	
    <div slot="actions" class="actions center">
        <Button outlined on:click="{()=>(close())}">Zavřít</Button>
    </div>
</Dialog>


<style>
a { cursor:pointer; }
textarea { width:99%; min-height:300px; }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
table { margin:auto; }
td, th { border:1px solid black; padding:5px; }
</style>
