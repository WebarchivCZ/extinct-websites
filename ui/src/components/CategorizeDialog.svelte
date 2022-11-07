<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Autocomplete from '@smui-extra/autocomplete';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import { api, db } from "../ConfigService.js";

  export let visible = false;
  export let uuid;
  export let type;
  export let category=false;
  export let needsUpdate;
  let value="";
  
  async function save() {
  	const response = await fetch($api+'action/group/?uuid='+uuid+'&category='+value+'&db='+$db);
  	close();
  	if(await response.json()) { needsUpdate=true; }
	return await response.json()
  }
  
  function getGroups(groups) {
  	let out=[];
  	for(let i=0; i<groups.length; i++) {
  		out.push(groups[i]);
  	}	
  	return out;
  }


 $: {
	if(uuid && type=="category" && category) { visible=true; }
 }
 
 function close() {
 	uuid=false;
 	visible=false;
 }
 
 
</script>

<Dialog width="600" bind:visible>
    <div slot="title">Zařazení do kategorie</div>
{#if category}	
	<div style="text-align:center; margin:auto; min-height:200px;">
		<Autocomplete combobox options={getGroups(category)} bind:value label="Název kategorie" />	
	</div>
{/if}
    <div slot="actions" class="actions center">
   	<Button color="primary" raised on:click="{()=>(save())}">Uložit</Button>&nbsp;&nbsp;&nbsp;&nbsp;
        <Button outlined on:click="{()=>(close())}">Zavřít</Button>
    </div>
</Dialog>


<style>
a { cursor:pointer; }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
</style>
