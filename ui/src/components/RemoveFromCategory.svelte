<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import { api, db } from "../ConfigService.js";

  export let visible = false;
  export let uuid;
  export let type;
  export let needsUpdate;
  export let active="";
  
  async function save() {
  	const response = await fetch($api+'action/removeFromGroup/?category='+active+'&db='+$db, {
		method: 'POST',
		body: JSON.stringify({
			uuid
		})
	});
  	close();
  	uuid=false;
  	if(await response.json()) { needsUpdate=true; }
	return await response.json()
  }


 $: {
	if(uuid && type=="removeFromCategory") { visible=true; }
 }
 
 function close() {
 	uuid=false;
 	visible=false;
 }
 
 
</script>

<Dialog width="600" bind:visible beforeClose={()=>close()}>
    <div slot="title">Odstranění z kategorie</div>
	<b>Opravdu si přejete odstranit vybrané položky z této kategorie?</b>
    <div slot="actions" class="actions center">
   	<Button color="accent" raised on:click="{()=>(save())}">ODSTRANIT</Button>&nbsp;&nbsp;&nbsp;&nbsp;
        <Button outlined on:click="{()=>(close())}">Zrušit</Button>
    </div>
</Dialog>


<style>
a { cursor:pointer; }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
</style>
