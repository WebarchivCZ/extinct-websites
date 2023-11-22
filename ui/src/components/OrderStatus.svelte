<script>
  import IoIosArrowDown from 'svelte-icons/io/IoIosArrowDown.svelte'
  import IoIosArrowUp from 'svelte-icons/io/IoIosArrowUp.svelte'

  export let id="";
  export let order;
  export let orderReverse;
  
  function setOrder(o, r) {
  	order=o;
  	orderReverse=r;
  }
  
  function getActiveClass(o, r, v) {
  	if(o==id && r==v) { return "orderIconActive"; }
  	return "";
  }
</script>

 <div class="orderIcon {getActiveClass(order, orderReverse, 1)}" on:click="{()=>setOrder(id, 1)}"><IoIosArrowDown /></div>
 <div class="orderIcon {getActiveClass(order, orderReverse, 0)}" on:click="{()=>setOrder(id, 0)}"><IoIosArrowUp /></div>


<style>
.orderIcon { width:13px; float:right; color:silver; cursor:pointer; }
.orderIconActive { color:black; }
</style>
