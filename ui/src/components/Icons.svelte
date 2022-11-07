<script>
	import IoIosCheckmark from 'svelte-icons/io/IoIosCheckmark.svelte'
	import IoIosClose from 'svelte-icons/io/IoIosClose.svelte'
	import IoMdInformationCircleOutline from 'svelte-icons/io/IoMdInformationCircleOutline.svelte'
	import IoMdToday from 'svelte-icons/io/IoMdToday.svelte'
	import IoMdContacts from 'svelte-icons/io/IoMdContacts.svelte'
	import IoMdFlashOff from 'svelte-icons/io/IoMdFlashOff.svelte'
	import GoTag from 'svelte-icons/go/GoTag.svelte'
	import IoIosTrendingDown from 'svelte-icons/io/IoIosTrendingDown.svelte'
	
	export let type=false;
</script> 

<div class="icon">
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
	{:else if type=="dead"}
		<span class="action"><IoMdFlashOff /></span>
	{:else if type=="category"}
		<span class="action"><GoTag /></span>		
	{/if}

</div>

<style>
	.icon, .icon svg { height:35px!important; max-height:35px!important; float:left; margin-right:20px; }
	.ok { color:green; }
	.error { color:red; }
	.action { color:var(--mdc-ripple-color,var(--mdc-theme-primary,#6200ee)); }
	.bold { font-weight:bold; }
</style>
