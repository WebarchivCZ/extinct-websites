<script>
	import IoIosCheckmark from 'svelte-icons/io/IoIosCheckmark.svelte'
	import IoIosClose from 'svelte-icons/io/IoIosClose.svelte'
	import IoMdInformationCircleOutline from 'svelte-icons/io/IoMdInformationCircleOutline.svelte'
	import IoMdToday from 'svelte-icons/io/IoMdToday.svelte'
	import IoMdContacts from 'svelte-icons/io/IoMdContacts.svelte'
	import IoMdFlashOff from 'svelte-icons/io/IoMdFlashOff.svelte'
	import GoTag from 'svelte-icons/go/GoTag.svelte'
	import IoIosTrendingDown from 'svelte-icons/io/IoIosTrendingDown.svelte'
	import IoMdArchive from 'svelte-icons/io/IoMdArchive.svelte'
	import IoMdGitCompare from 'svelte-icons/io/IoMdGitCompare.svelte'
	import IoMdHelp from 'svelte-icons/io/IoMdHelp.svelte'
	export let type=false;
	export let title="";
</script> 

<div class="icon" title>
	{#if type=="ok"}
		<span class="ok bold"><IoIosCheckmark /></span>
	{:else if type=="no"}
		<span class="error bold"><IoIosClose /></span>
	{:else if type=="down"}
		<span class="error bold"><IoIosTrendingDown /></span>
	{:else if type=="info"}
		<IoMdInformationCircleOutline />
	{:else if type=="page"}
		<IoMdToday />
	{:else if type=="contact"}
		<IoMdContacts />
	{:else if type=="archive"}
		<span class="info"><IoMdArchive /></span>
	{:else if type=="dead"}
		<span class="action"><IoMdFlashOff /></span>
	{:else if type=="category"}
		<span class="action"><GoTag /></span>	
	{:else if type=="verify"}
		<span class="action"><IoMdGitCompare /></span>	
	{:else if type=="question"}
		<span class="silver"><IoMdHelp /></span>		
	{/if}

</div>

<style>
	.icon, .icon svg { height:35px!important; max-height:35px!important; display:inline-block; margin-right:10px; bottom:3px; }
	.ok { color:green; }
	.error { color:red; }
	.silver { color:silver; }
	.info { color:rgb(1, 135, 134); }
	.action { color:var(--mdc-ripple-color,var(--mdc-theme-primary,#6200ee)); }
	.bold { font-weight:bold; }
</style>
