<script>
  import { Button, Dialog } from 'svelte-mui/src';
  import Select, { Option } from '@smui/select';
  import Loading from "../components/Loading.svelte";
  import IoIosAdd from 'svelte-icons/io/IoIosAdd.svelte'
  import { api, db } from "../ConfigService.js";

  export let visible = false;
  export let url;
  export let type;
  export let active;
  
  export let month="*";
  export let day="*"; 
  export let hour=3;
  
  async function save() {
  	const response = await fetch($api+'action/setAutoCheck/?db='+$db, {
		method: 'POST',
		body: JSON.stringify({
			group: active,
			month:month,
			day:day,
			hour:hour
		})
	});
  	close();
	return await response.json()
  }
  
  async function removeAutoCheck() {
  	const response = await fetch($api+'action/setAutoCheck/?db='+$db, {
		method: 'POST',
		body: JSON.stringify({
			group: active,
			month: -1,
			day: -1,
			hour: -1
		})
	});
  	close();
	return await response.json()
  }
  


 $: {
	if(type=="autoCheck") { 
		visible=true; 
		if(active && active!="VŠE") { loadSettings(); }
	}
 }
 
 function close() {
 	visible=false;
 	type=false;
 }
 
  async function loadSettings() {
  	const response = await fetch($api+'groups/?db='+$db+'&group='+encodeURI(active));
	const data = await response.json();
	setDefault(await data);
  }
  
  function setDefault(data) {
  console.log(data);
  	if(data) {
	  	let spl=data[0]['check'].split(" ");
	  	month=spl[0];
	  	day=spl[1];
	  	hour=spl[2];
	}
  	
  }
 
 
</script>

<Dialog width="700" bind:visible style="min-height:600px;" beforeClose={()=>close()}>
    <div slot="title">Nastavit automatickou kontrolu kategorie {active}</div>

    <div class="columns margins" style="max-heigth:250px;heigth:250px;">
	    <Select bind:value={month} label="Měsíc" style="height:400px!important;overflow:scroll;">
		<Option value="*">Každý</Option>
		<Option value="1">Leden</Option>
		<Option value="2">Únor</Option>
		<Option value="3">Březen</Option>
		<Option value="4">Duben</Option>
		<Option value="5">Květen</Option>
		<Option value="6">Červen</Option>
		<Option value="7">Červenec</Option>
		<Option value="8">Srpen</Option>
		<Option value="9">Září</Option>
		<Option value="10">Říjen</Option>
		<Option value="11">Listopad</Option>
		<Option value="12">Prosinec</Option>
	    </Select>
	    
	    <Select bind:value={day} label="Den" style="height:400px!important;overflow:scroll;">
		<Option value="*">Každý</Option>
		{#each Array(31) as _, i}
			<Option value={i+1}>{i+1}</Option>
	      	{/each}
	    </Select>

	    <Select bind:value={hour} label="Hodina" style="height:400px!important;overflow:scroll;">
		<Option value="*">Každá</Option>
		{#each Array(24) as _, i}
			<Option value={i}>{i}</Option>
	      	{/each}
	    </Select>
    </div>
    
    {#if !month && !day && !hour}
    	<b>Automatická kontrola nebyla dosud nastavena.</b>
    {/if}
	
    <br /><br /><br />
    <div slot="actions" class="actions center">
   	<Button color="primary" raised on:click="{()=>(save())}">Nastavit</Button>&nbsp;&nbsp;&nbsp;&nbsp;
   	<Button color="red" raised on:click="{()=>(removeAutoCheck())}">Zrušit kontrolu</Button>&nbsp;&nbsp;&nbsp;&nbsp;
        <Button outlined on:click="{()=>(close())}">Zavřít</Button>
    </div>
</Dialog>


<style>
a { cursor:pointer; }
textarea { width:99%; min-height:300px; }
.outlined { height:60px; }
.key { font-weight:bold; }
.subcategory { margin-left:50px; }
</style>
