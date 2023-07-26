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
  	import AddUrlDialog from "./components/AddUrlDialog.svelte";
  	import AutoCheckDialog from "./components/AutoCheckDialog.svelte";
  	import CategorizeDialog from "./components/CategorizeDialog.svelte";
  	import RemoveFromCategory from "./components/RemoveFromCategory.svelte";
  	import DeadDialog from "./components/DeadDialog.svelte";
  	import Icon from "./components/Icons.svelte";
  	import Pagination from "./components/Pagination.svelte";
  	import LimitSelect from "./components/LimitSelect.svelte";
  	import Notify from "./components/Notify.svelte";
  	import OrderStatus from "./components/OrderStatus.svelte";
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
  export let order="id";
  export let orderReverse=false;
  
  export let page=1;
  export let limit=15;
  
  export let notify="";
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
  	if(order) {
  		param+='&order='+order;
  		if(orderReverse) { param+='&orderReverse=1'; }
  	} 
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
  		if(groups[i]!=out[0]) { out.push(groups[i]); }
  	}	
  	return out;
  }
  
 onMount(() => {
 	loadData();
 });
 
 $: {
 	if(active) { loadData(filterUrl, filterType, page, limit, filterDateFrom, filterDateTo, order, orderReverse); }
 	if(needsUpdate) { 
 		checkbox=[];
 		loadData(needsUpdate); 
 		updateGroups(needsUpdate);
 		needsUpdate=0;
 	}
 }
 
  async function addRequest() {
  	const response = await fetch($api+'action/addRequest/?db='+$db, {
		method: 'POST',
		body: JSON.stringify({
			uuid
		})
	});
  	if(await response.json()) { 
  		//needsUpdate=true;
  		checkbox=[]; 
  		notify = "Proces kontroly byl naplánován!";
  	}
  }
 
 
 function openDialog(u, t, a) {
  	type=t;
 	uuid=u;
 	url=a;
 	if(t=="request") { addRequest(); }
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
 
 function selectAll(d) {
 	if(checkbox.length==d['data'].length) {
	 	checkbox=[];
 	
 	} else {
	 	for(let i=0; i<d['data'].length; i++) {
	 		checkbox[i]=d['data'][i]['UUID'];
	 	}
	}
 	return false;
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
		<div class="container-fluid" style="max-width:1270px!important;">
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
					    <th><OrderStatus id="id" bind:order bind:orderReverse /></th>
					    <th>URL <OrderStatus id="url" bind:order bind:orderReverse /></th>
					    <th style="min-width:205px;">Info</th>
					    <th>Poslední kontrola <OrderStatus id="checkTime" bind:order bind:orderReverse /></th>
					    <th>Index úmrtí <OrderStatus id="index" bind:order bind:orderReverse /></th>
					    <th>Stav</th>
					    <th>Datum úmrtí <OrderStatus id="deadTime" bind:order bind:orderReverse /></th>
					    <!--<th>Kategorie</th>-->
					    <th style="min-width:80px;">Kontrola</th>
					</tr>
				    </thead>
				    <tbody>
				  	{#each data.data as row,row_id}
				  		<tr>
				  			<td><label><input type=checkbox bind:group={checkbox} value={row.UUID} class="selectCheckbox"></label></td>
				  			<td><a href="{getUrl(row.url)}" target="web">{showUrl(row.url)}</a></td>
				  			<td class="click">
				  				<span on:click="{()=>openDialog(row.UUID, 'harvest', row.url)}"><Icon type="info" title="Harvest data" /></span>
				  				<span on:click="{()=>(openDialog(row.UUID, 'page', row.url))}" style="{getMetadataStyle(row.status.metadata, row.status.date)}"><Icon type="page" title="Page data" /></span>
				  				<span on:click="{()=>(openDialog(row.UUID, 'whois', row.url))}" style="{getMetadataStyle(row.status.whois, row.status.date)}"><Icon type="contact" title="Whois" /></span>
				  				<span><a href="https://wayback.webarchiv.cz/secure/*/{row.url}" target="webarchiv"><Icon type="archive" title="Webarchiv" /></a></span>
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
				  						<span on:click="{()=>(openDialog(row.UUID, 'alive', row.url))}"><Icon type="no" title="Označit za živý" /></span>
				  					{:else if row.status.dead && row.status.dead!="0" || row.status.requires=="1"}
				  						<span on:click="{()=>(openDialog(row.UUID, 'unknown', row.url))}"><Icon type="down" title="Označit za mrtvý / živý" /></span>
				  					{:else}
				  						<span on:click="{()=>(openDialog(row.UUID, 'dead', row.url))}"><Icon type="ok" title="Označit za mrtvý" /></span>
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
				  				<span on:click="{()=>(openDialog(row.UUID, 'verify', row.url))}"><Icon type="verify" title="Zkontrolovat metadata webu" /></span>&nbsp;&nbsp;&nbsp;
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
					<Button on:click={() => (openDialog(checkbox, "request", false))} variant="raised">
					  <Label>Vyžádat kontrolu</Label>
					</Button>
					<br><br><a href="#" onclick="return false;" on:click={() => selectAll(data)}>Vybrat vše / zrušit výběr</a>
					{#if data.stats.sum}
						<div style="float:right;width:200px;text-align:right;">záznamů: {data.stats.sum}</div>
						<br style="clear:both" />
					{/if}
				</div>
				<Button on:click={() => window.open($api+'?db='+$db+'&page='+(page-1)+'&limit='+limit+getParams(), "_blank")} variant="unelevated" color="secondary">
				  <Label>Zobrazit data jako JSON</Label>
				</Button>
			</div>
		    </div>
		    <br />
		    <div style="margin:auto;text-align:center;">
		    	  {#if active && active!="VŠE"}
		    	  	<b>Akce pro kategorii:&nbsp;&nbsp;</b>
				<Button on:click={() => (openDialog(false, "autoCheck", false))} variant="raised">
					<Label>Nastavit automatickou kontrolu</Label>
				</Button>
			  {/if}
			  <Button on:click={() => (openDialog(false, "addUrl", false))} variant="raised">
			  	<Label>Přidat URL</Label>
			  </Button>
		</div>
		<br /><br />
		    
	  {:else}
	  	<Loading />
	  {/if}

	<br>
	<CategorizeDialog bind:uuid bind:type bind:needsUpdate category={groups} />
	<RemoveFromCategory bind:uuid bind:type bind:needsUpdate bind:active />
	<DeleteDialog bind:uuid bind:type bind:needsUpdate />
	<AddUrlDialog bind:active bind:type bind:needsUpdate />
	<AutoCheckDialog bind:active bind:type />
	<DeadDialog bind:uuid bind:type bind:needsUpdate bind:url data={data} />
{/await}
<DataDialog bind:uuid bind:url bind:type />
<VerifyDialog bind:url bind:type bind:needsUpdate />
<Notify bind:notify />

</main>
<footer>
	Extinct Websites v1.2
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
