<script lang="ts">
// autor Jan Holomek (jahhoo@gmail.com)

	import { Router, Link, Route } from "svelte-routing";
	import { onMount } from "svelte";
  	import Textfield from '@smui/textfield';
  	import Button from '@smui/button';
	import Tab, { Label } from '@smui/tab';
  	import TabBar from '@smui/tab-bar';
  	import { paginate, LightPaginationNav } from 'svelte-paginate'
  	import NavLink from "./components/NavLink.svelte";
  	//import Login from "./components/Login.svelte";
  	import Loading from "./components/Loading.svelte";
  	import DataDialog from "./components/DataDialog.svelte";
  	import CategorizeDialog from "./components/CategorizeDialog.svelte";
  	import DeadDialog from "./components/DeadDialog.svelte";
  	import Icon from "./components/Icons.svelte";
  	import LimitSelect from "./components/LimitSelect.svelte";
  	import { api, db } from "./ConfigService.js";

  export let urlPath = "";
  let path="";
  export let data=false;
  export let uuid=false;
  export let type=false;
  export let filterUrl="";
  export let filterType="";
  
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
  	return param;
  }

  async function loadData() {
  	const response = await fetch($api+'?db='+$db+'&type=app&page='+(page-1)+'&limit='+limit+getParams());
	data = await response.json();
  }
	
  function getGroups(groups) {
  	let out=[];
  	out[0]="VŠE";
  	for(let i=0; i<groups.length; i++) {
  		out.push(groups[i]);
  	}	
  	return out;
  }
  
 //    <Login bind:login />
 onMount(() => {
 	loadData();
	//updateLogin();
 });
 $: {
 	if(active) { loadData(filterUrl, filterType, page, limit); }
 	if(needsUpdate) { 
 		loadData(needsUpdate); 
 		needsUpdate=0;
 	}
 }
 
 function openDialog(u, t) {
 	uuid=u;
 	type=t;
 }
 
 function getUrl(url) {
 	if(!url.includes("http://") && !url.includes("https://")) { return "http://"+url; }
 	return url;
 }
 
 //dodělat stránkování
 
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
	<Router bind:url={urlPath}> 

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
			    	&nbsp;&nbsp;
			    	<select bind:value={filterType}>
					<option value="">VŠE</option>
					<option value="dead">Mrtvé weby</option>
					<option value="unknown">Ke kontrole</option>
					<option value="live">Živé weby</option>
				</select>
			    	
			    </div>
			    {#if data.stats}
			      {#if data.stats.sum}
				    <div class="float-right">
				    	<LightPaginationNav
					  totalItems="{data.stats.sum}"
					  bind:pageSize="{limit}"
					  bind:currentPage={page}
					  limit=3
					  showStepOptions=true
					   on:setPage="{(e) => page = e.detail.page}"
					/>				    
				    </div>
				    <div class="float-right px-2">
				    	<LimitSelect bind:limit />
				    </div>
			      {/if}
			    {/if}
			</div>
				<table class="table data-table m-0">
				    <thead>
					<tr>
					    <th>URL</th>
					    <th>Data</th>
					    <th>Poslední kontrola</th>
					    <th>Stav</th>
					    <th>Kategorie</th>
					</tr>
				    </thead>
				    <tbody>
				  	{#each data.data as row,row_id}
				  		<tr>
				  			<td><a href="{getUrl(row.url)}" target="web">{row.url}</a></td>
				  			<td class="click">
				  				<span title="Harvest data" on:click="{()=>openDialog(row.UUID, 'harvest')}"><Icon type="info" /></span>&nbsp;&nbsp;&nbsp;
				  				<span title="Page data" on:click="{()=>(openDialog(row.UUID, 'page'))}"><Icon type="page" /></span>&nbsp;&nbsp;&nbsp;
				  				<span title="Whois" on:click="{()=>(openDialog(row.UUID, 'whois'))}"><Icon type="contact" /></span>&nbsp;&nbsp;&nbsp;
				  			</td>
				  			<td>
				  				{#if row.status.date}
				  					{row.status.date}
				  				{/if}
				  			</td>
				  			<td class="click">
				  				{#if row.status}
				  					{#if row.status.dead && row.status.confirmed && row.status.dead!="0"}
				  						<span title="Označit za živý" on:click="{()=>(openDialog(row.UUID, 'alive'))}"><Icon type="no" /></span>
				  					{:else if row.status.dead && row.status.dead!="0" || row.status.requires=="1"}
				  						<span title="Označit za mrtvý / živý" on:click="{()=>(openDialog(row.UUID, 'unknown'))}"><Icon type="down" /></span>
				  					{:else}
				  						<span title="Označit za mrtvý" on:click="{()=>(openDialog(row.UUID, 'dead'))}"><Icon type="ok" /></span>
				  					{/if}
				  				{/if}
				  			</td>
				  			<td class="click">
				  				<span title="Zařadit do kategorie" on:click="{()=>(openDialog(row.UUID, 'category'))}"><Icon type="category" /></span>&nbsp;&nbsp;&nbsp;
				  			</td>
				  		</tr>
				  	{/each}
			  	    </tbody>
				</table>
				<Button on:click={() => window.open($api+'?db='+$db+'&page='+(page-1)+'&limit='+limit+getParams(), "_blank")} variant="unelevated">
				  <Label>Zobrazit data jako JSON</Label>
				</Button>
			</div>
		    </div>
		    
	  {:else}
	  	<Loading />
	  {/if}

	  <div>
	    <br>
	    <Route path="/{path}">

	    	<br><br>

	    	<br>
	    </Route>
	    <Route path="/{path}notDnnt">TEST</Route>

	  </div>
	</Router>
	<CategorizeDialog bind:uuid bind:type bind:needsUpdate category={groups} />
	<DeadDialog bind:uuid bind:type bind:needsUpdate data={data} />
{/await}
<DataDialog bind:uuid bind:type />

</main>
<footer>
	Extinct Websites
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

.card-header h6 {
	padding: 10px 0;
}

.container-fluid {
	max-width:1200px;
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

</style>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" crossorigin="anonymous">
