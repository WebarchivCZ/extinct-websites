<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Autocomplete from '@smui-extra/autocomplete';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import { api, db } from "../ConfigService.js";

  export let visible = false;
  export let uuid;
  export let type;
  export let data=false;
  export let needsUpdate;
  let value="";
  
  async function save(dead) {
  	const response = await fetch($api+'/action/dead/?uuid='+uuid+'&dead='+dead);
  	close();
  	needsUpdate=true;
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
	if(uuid && (type=="dead" || type=="alive" || type=="unknown") && data) { visible=true; }
 }
 
 function close() {
 	uuid=false;
 	visible=false;
 }
 
 
</script>

<Dialog width="600" bind:visible>
    <div slot="title">Označení mrtvého webu</div>
{#if data}	
	

{/if}
    <div slot="actions" class="actions center">
    	{#if type=="unknown"}
    	   	<Button color="accent" raised on:click="{()=>(save(1))}">Označit za mrtvý</Button>&nbsp;&nbsp;&nbsp;&nbsp;
    	   	<Button color="green" raised on:click="{()=>(save(0))}">Označit za živý</Button>
    	{:else if type=="dead"}
   		<Button color="accent" raised on:click="{()=>(save(1))}">Označit za mrtvý</Button>
   	{:else}
   		<Button color="green" raised on:click="{()=>(save(0))}">Označit za živý</Button>
   	{/if}
   	&nbsp;&nbsp;&nbsp;&nbsp;
        <Button outlined on:click="{()=>(close())}">Zavřít</Button>
        <br />
    </div>
</Dialog>


<style>
a { cursor:pointer; }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
</style>
