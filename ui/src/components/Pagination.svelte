<script>
 export let page=1;
 export let records=0;
 export let limit=100;
 
 export let maxPages=14;
 
 let countPage=Math.ceil(records/limit);
 
 function getPages() {
 	let pages=[];
 	
 	for(let i=(page-Math.ceil(maxPages/2)); i<=page; i++) {
 		if(i>0 && (i<=Math.ceil(records/limit))) { pages[pages.length]=i; }
 	}
 	if(pages.length>0) { pages[pages.length]=0; }
 	
 	for(let i=(page+1); i<(page+Math.ceil(maxPages/2)+1); i++) {
 		if(i<=Math.ceil(records/limit)) { pages[pages.length]=i; }
 	}
 	console.log(pages);
 	return pages;
 }
 
  $: {
 	if(countPage && (page*limit)>records) { page=countPage; }
 }
 
</script>

{#if countPage}
<center>
  Strany: 
    {#if getPages()[0]!=1}
    	<a on:click="{()=>(page=1)}" style="cursor:pointer">1</a>&nbsp;&nbsp;
    	&middot;
    	&nbsp;&nbsp;
    {/if}
    {#each getPages() as p}
  	{#if page==p}
  		&middot;&nbsp;&nbsp; {p}
  	{:else if p==0}
  		&middot;
  	{:else}
  		<a on:click="{()=>(page=(p))}" style="cursor:pointer">{p}</a>
  	{/if}
  	&nbsp;&nbsp;
  {/each}
    {#if getPages().slice(-1)[0]!=countPage && countPage && countPage>0 && countPage!=page}	
    	&middot;
    	&nbsp;&nbsp;
    	<a on:click="{()=>(page=(countPage))}" style="cursor:pointer">{countPage}</a>
    {/if}
  
</center>
{/if}

<style>
a, a:link  { color: rgb(0,80,160)!important; text-decoration:underline!important; }
</style>
