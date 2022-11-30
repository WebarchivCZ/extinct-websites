<script>
 export let page=1;
 export let records=0;
 export let limit=100;
 export let maxPages=14;
 export let pages=false;
 
 let countPage=Math.ceil(records/limit);
 
 function getPages() {
 	pages=[];
 	let pagesBefore=0;
 	let pagesAfter=0;
 	countPage=Math.ceil(records/limit);
 	
 	pagesAfter=page+Math.round(maxPages/2);
 	if(pagesAfter>=countPage) {
 		pagesBefore+=countPage-pagesAfter;
 		pagesAfter=countPage;
 	}
 	pagesBefore+=page-Math.round(maxPages/2);
 	if(pagesBefore<1) {
 		pagesAfter+=0-pagesBefore;
 		if(pagesAfter>=countPage) { pagesAfter=countPage; }
 		pagesBefore=1;
 	}
 	
 	for(let i=pagesBefore; i<=pagesAfter; i++) {
 		pages[pages.length]=i; 
 	}
 	return "";
 }
 
 function goToPage(p) {
 	page=p;
 	getPages();
 }
 
  $: {
 	if(page>countPage) { page=countPage; }
 	else if(page<=0) { page=1; }
 	if(records) { getPages(records); }
 }
 
</script>

{#if countPage}
<div class="pagination">
 <span class="page" on:click="{()=>(goToPage(page-1))}">&lt;</span>
    {getPages()}
    {#if pages}
	    {#if pages[0]!=1}
	    	<span class="page" on:click="{()=>(goToPage(1))}">1</span>
	    {/if}
	    {#each pages as p}
		  	{#if page==p}
		  		<span class="page pageActive">{p}</span>
		  	{:else if p!=0}
		  		<span class="page" on:click="{()=>(goToPage(p))}">{p}</span>
		  	{/if}
		 
	    {/each}
	    {#if pages.slice(-1)[0]!=countPage && countPage && countPage>0 && countPage!=page}	
	    	<span class="page" on:click="{()=>(goToPage(countPage))}">{countPage}</span>
	    {/if}
    {/if}
 <span class="page" on:click="{()=>(goToPage(page+1))}">&gt;</span>
</div>
{/if}

<style>
.pagination {
	display: flex;
	justify-content: center;
	background: #FFF;
	border-radius: 3px;
	box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}
.page {
	padding: 10px 12px;
	cursor:pointer
}
.page:hover {
  background: rgba(0, 0, 0, 0.1);
  cursor: pointer;
}
.pageActive  { 
	color: hsl(200, 70%, 50%)!important; 
}
</style>
