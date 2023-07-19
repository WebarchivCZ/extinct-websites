<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import { api, db } from "../ConfigService.js";

  export let visible = false;
  export let url;
  export let type;
  export let active;
  export let needsUpdate;
  
  async function save() {
  	let group=active;
  	if(group=="VŠE") { group=""; }
  	const response = await fetch($api+'action/addUrl/?db='+$db, {
		method: 'POST',
		body: JSON.stringify({
			url:url.replace(",,",",").replace("\n",",").replace(" ",","),
			group
		})
	});
  	if(await response.json()) { needsUpdate=true; }
  	close();
	return await response.json()
  }


 $: {
	if(type=="addUrl") { visible=true; }
 }
 
 function close() {
 	visible=false;
 	type=false;
 }
 
 
</script>

<Dialog width="600" bind:visible beforeClose={()=>close()}>
    <div slot="title">Přidání URL do kategorie {active}</div>
	<textarea bind:value="{url}" />
	
    <div slot="actions" class="actions center">
   	<Button color="primary" raised on:click="{()=>(save())}">Přidat</Button>&nbsp;&nbsp;&nbsp;&nbsp;
        <Button outlined on:click="{()=>(close())}">Zrušit</Button>
    </div>
</Dialog>


<style>
a { cursor:pointer; }
textarea { width:99%; min-height:300px; }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
</style>
