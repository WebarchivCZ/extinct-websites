<script lang="ts">
// autor Jan Holomek (jahhoo@gmail.com)

	import { onMount } from "svelte";
  	import Textfield from '@smui/textfield';
  	import Button from '@smui/button';
	import Tab, { Label } from '@smui/tab';
  	import TabBar from '@smui/tab-bar';
  	import NavLink from "./components/NavLink.svelte";
  	import Loading from "./components/Loading.svelte";
  	import DataDialog from "./components/DataDialog.svelte";
  	import VerifyDialog from "./components/VerifyDialog.svelte";
  	import DateRange from "./components/DateRange.svelte";
  	import DeleteDialog from "./components/DeleteDialog.svelte";
  	import CategorizeDialog from "./components/CategorizeDialog.svelte";
  	import RemoveFromCategory from "./components/RemoveFromCategory.svelte";
  	import DeadDialog from "./components/DeadDialog.svelte";
  	import Icon from "./components/Icons.svelte";
  	import Pagination from "./components/Pagination.svelte";
  	import LimitSelect from "./components/LimitSelect.svelte";
  	import { api, db } from "./ConfigService.js";

  export let urlPath = "";
  let path="";
  export let data=false;
  export let uuid=false;
  export let url=false;
  export let type=false;
  export let filterUrl="";
  export let filterType="";
  export let checkbox=[];
  export let filterDateFrom=false;
  export let filterDateTo=false;
  
  export let page=1;
  export let limit=15;
  
  export let needsUpdate=false;
  
  export let tab=false;
  export let active="VŠE";
  export let groups = (async () => {
	    const response = await fetch($api+'groups/?db='+$db);
	    return await response.json()
	})()

  function getParams() {
  	let param="";
  	if(active && active!="VŠE") { param+='&kat='+active; }
  	if(filterUrl && filterUrl!="") { param+='&search='+filterUrl; }
  	if(filterType && filterType!="") { param+='&filter='+filterType; }
  	if(filterDateFrom && filterDateFrom!="") { param+='&dateFrom='+filterDateFrom; }
  	if(filterDateTo && filterDateTo!="") { param+='&dateTo='+filterDateTo; }
  	return param;
  }

  async function loadData() {
  	const response = await fetch($api+'?db='+$db+'&type=app&page='+(page-1)+'&limit='+limit+getParams());
	data = await response.json();
	changePageIfNotExists(await data);
  }
  
  async function updateGroups() {
  	const response = await fetch($api+'groups/?db='+$db);
	groups = await response.json();
  }
  
  function changePageIfNotExists(d) {
  	if(data.stats) {
  		if(data.stats.sum) {
  			let maxPages=Math.ceil(parseInt(data.stats.sum)/limit);
  			if(page>maxPages) { page=maxPages; }
  		}
  	}
  }
	
  function getGroups(groups) {
  	let out=[];
  	out[0]="VŠE";
  	for(let i=0; i<groups.length; i++) {
  		out.push(groups[i]);
  	}	
  	return out;
  }
  
 onMount(() => {
 	loadData();
 });
 
 $: {
 	if(active) { loadData(filterUrl, filterType, page, limit, filterDateFrom, filterDateTo); }
 	if(needsUpdate) { 
 		loadData(needsUpdate); 
 		updateGroups(needsUpdate);
 		needsUpdate=0;
 	}
 }
 
 function openDialog(u, t, a) {
  	type=t;
 	uuid=u;
 	url=a;
 }
 
 function getUrl(url) {
 	if(!url.includes("http://") && !url.includes("https://")) { return "http://"+url; }
 	return url;
 }
 
 function showUrl(url) {
 	url=url.replaceAll("index.html", ""); 
 	url=url.replaceAll("index.htm", ""); 
 	url=url.replaceAll("index.php", ""); 
 	url=url.replaceAll("index.asp", ""); 
 	return url; 
 }
 
 function getMetadataStyle(value, date) {
 	if(value=="0" || date && !value) { return "color:silver"; }
 	return "";
 }
 
</script>

<svelte:head>
	<title>Extinct Websites</title>
	<meta name="robots" content="noindex nofollow" />
	<html lang="cs" />
</svelte:head>



<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/svelte-material-ui@6.0.0/bare.min.css"
/>

<main>
{#await groups}
	<Loading />
{:then groups}
	  {#if groups}
		  <div>
			  <TabBar tabs={getGroups(groups)} let:tab bind:active>
			    <Tab {tab}>
			      <Label>{tab}</Label>
			    </Tab>
			  </TabBar>
		  </div>
	  {/if}
	  
	  <br><br>
	  {#if data}
		<div class="container-fluid">
		    <div class="card bg-light mb-2 overflow-hidden">
			<div class="card-header px-2 py-0">
			    <div class="float-left">
			    	<Textfield variant="filled" bind:value={filterUrl} label="vyhledat dle URL"></Textfield>
			     </div>
			     <div class="float-left filterSelect">
			    	<select bind:value={filterType}>
					<option value="">VŠE</option>
					<option value="dead">Mrtvé weby</option>
					<option value="unknown">Ke kontrole</option>
					<option value="live">Živé weby</option>
				</select>
			     </div>
			     <div class="float-left filterDataRange">
				<DateRange bind:from={filterDateFrom} bind:to={filterDateTo} />
			    </div>
			    {#if data.stats}
			      {#if data.stats.sum}
				    <div class="float-right">
				    	<Pagination 
				    		bind:page
				    		bind:records="{data.stats.sum}"
				    		maxPages=6
				    		bind:limit		    	
				    	/>				    
				    </div>
				    <div class="float-right px-1">
				    	<LimitSelect bind:limit />
				    </div>
			      {/if}
			    {/if}
			</div>
				<table class="table data-table m-0">
				    <thead>
					<tr>
					    <th></th>
					    <th>URL</th>
					    <th>Info</th>
					    <th>Poslední kontrola</th>
					    <th>Index úmrtí</th>
					    <th>Stav</th>
					    <th>Datum úmrtí</th>
					    <!--<th>Kategorie</th>-->
					    <th>Ověřit</th>
					</tr>
				    </thead>
				    <tbody>
				  	{#each data.data as row,row_id}
				  		<tr>
				  			<td><label><input type=checkbox bind:group={checkbox} value={row.UUID} class="selectCheckbox"></label></td>
				  			<td><a href="{getUrl(row.url)}" target="web">{showUrl(row.url)}</a></td>
				  			<td class="click">
				  				<span title="Harvest data" on:click="{()=>openDialog(row.UUID, 'harvest', row.url)}"><Icon type="info" /></span>
				  				<span title="Page data" on:click="{()=>(openDialog(row.UUID, 'page', row.url))}" style="{getMetadataStyle(row.status.metadata, row.status.date)}"><Icon type="page" /></span>
				  				<span title="Whois" on:click="{()=>(openDialog(row.UUID, 'whois', row.url))}" style="{getMetadataStyle(row.status.whois, row.status.date)}"><Icon type="contact" /></span>
				  				<span title="Webarchiv"><a href="https://wayback.webarchiv.cz/secure/*/{row.url}" target="webarchiv"><Icon type="archive" /></a></span>
				  			</td>
				  			<td>
				  				{#if row.status.date}
				  					{row.status.date}
				  				{/if}
				  			</td>
				  			<td>
				  				{#if row.status.metadata=="0"}
				  					x
				  				{:else if row.status.metadata_match}
				  					{row.status.metadata_match}
				  				{/if}
				  			</td>
				  			<td class="click">
				  				{#if row.status}
				  					{#if row.status.dead && row.status.confirmed && row.status.dead!="0" || row.exticint.date}
				  						<span title="Označit za živý" on:click="{()=>(openDialog(row.UUID, 'alive', row.url))}"><Icon type="no" /></span>
				  					{:else if row.status.dead && row.status.dead!="0" || row.status.requires=="1"}
				  						<span title="Označit za mrtvý / živý" on:click="{()=>(openDialog(row.UUID, 'unknown', row.url))}"><Icon type="down" /></span>
				  					{:else}
				  						<span title="Označit za mrtvý" on:click="{()=>(openDialog(row.UUID, 'dead', row.url))}"><Icon type="ok" /></span>
				  					{/if}
				  				{/if}
				  			</td>
				  			<td>
				  				{#if row.exticint.date}
				  					{row.exticint.date}
				  				{/if}
				  			</td>
				  			<!--<td class="click">
				  				<span title="Zařadit do kategorie" on:click="{()=>(openDialog(row.UUID, 'category', false))}"><Icon type="category" /></span>&nbsp;&nbsp;&nbsp;
				  			</td>-->
				  			<td class="click">
				  				<span title="Ověřit dostupnost webu" on:click="{()=>(openDialog(row.UUID, 'verify', row.url))}"><Icon type="verify" /></span>&nbsp;&nbsp;&nbsp;
				  			</td>
				  		</tr>
				  	{/each}
			  	    </tbody>
				</table>
				<div class="tableFooter">
					<b>Vybrané:&nbsp;&nbsp;</b>
					<Button on:click={() => (openDialog(checkbox, "delete", false))} variant="raised">
					  <Label>Odstranit</Label>
					</Button>
					<Button on:click={() => (openDialog(checkbox, "category", false))} variant="raised">
					  <Label>Zařadit do kategorie</Label>
					</Button>
					<Button on:click={() => (openDialog(checkbox, "removeFromCategory", false))} variant="raised">
					  <Label>Odstranit z kategorie</Label>
					</Button>
					<Button on:click={() => (openDialog(checkbox, "unknown", false))} variant="raised">
					  <Label>Označit za mrtvé / živé</Label>
					</Button>
				</div>
				<Button on:click={() => window.open($api+'?db='+$db+'&page='+(page-1)+'&limit='+limit+getParams(), "_blank")} variant="unelevated" color="secondary">
				  <Label>Zobrazit data jako JSON</Label>
				</Button>
			</div>
		    </div>
		    
	  {:else}
	  	<Loading />
	  {/if}

	<br>
	<CategorizeDialog bind:uuid bind:type bind:needsUpdate category={groups} />
	<RemoveFromCategory bind:uuid bind:type bind:needsUpdate bind:active />
	<DeleteDialog bind:uuid bind:type bind:needsUpdate />
	<DeadDialog bind:uuid bind:type bind:needsUpdate bind:url data={data} />
{/await}
<DataDialog bind:uuid bind:url bind:type />
<VerifyDialog bind:url bind:type bind:needsUpdate />

</main>
<footer>
	Extinct Websites v1.0
</footer>

<style>
a, .click { cursor:pointer; }
.settings { float:right; }
main {
  position: relative;
  min-height: 100vh;
  padding-bottom:-50px;
  margin-bottom:-30px;
}

footer {
  font-size:9px;
  text-align:right;  
  position: relative;
  top:0px;
  padding-bottom:-50px;
}

input.selectCheckbox {
    width: 22px;
    height: 22px;
}

.filterSelect {
    position: relative;
    top: 10px;
    margin-left: 5px;
}

.filterDataRange {
    position: relative;
    top: 16px;
    margin-left: 5px;
}

.tableFooter {
	border-top:1px solid rgba(0,0,0,.125);
	padding: 30px 20px 40px 20px;
	background-color:rgba(128,128,128,0.3);
}

.card-header h6 {
	padding: 10px 0;
}

.container-fluid {
	max-width:1200px;
}

.data-table {
}

.data-table th:first-child {
	width: 50px;
}

.data-table td {
	padding-top: .25rem;
	padding-bottom: .25rem
}

.data-table tr:nth-child(2n) {
	background-color:rgba(255,255,255,0.9);
}

select { min-height:45px !important; }

.small { height:20px; }
</style>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" crossorigin="anonymous">
