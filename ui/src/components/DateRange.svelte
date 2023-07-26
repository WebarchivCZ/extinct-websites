<script>
 import Flatpickr from 'svelte-flatpickr';
 import 'flatpickr/dist/flatpickr.css';
 import 'flatpickr/dist/themes/material_blue.css';
 import 'flatpickr/dist/l10n/cs.js';
 
 
 export let from=false;
 export let to=false;
 
const flatpickrOptions = {
  enableTime: true,
  onChange: (selectedDates, dateStr, instance) => {}
};
function handleChange(event) {
	const [ selectedDates ] = event.detail;
	if(selectedDates[0]) { from=dateToDMY(selectedDates[0]); }
	if(selectedDates[1]) { to=dateToDMY(selectedDates[1]);  }
}

export let date=false;

let flatpickrOptionsRange = {
  mode: "range",
  enableTime: false,
  dateFormat: "d.m.Y",
  altFormat: "d.m.Y",
  maxDate: new Date().fp_incr(0),
  locale: "cs",
  onChange: (selectedDates, dateStr, instance) => {}
};


function dateToDMY(date) {
    var d = date.getDate();
    var m = date.getMonth() + 1;
    var y = date.getFullYear();
    return y+ '-' + (m<=9 ? '0' + m : m) + '-' + (d <= 9 ? '0' + d : d) ;
}

function reset() {
	from=false;
	to=false;
}

</script>

<div class="float-left range-picker" label="Range picker">
 <label class="mdc-text-field mdc-text-field--filled mdc-ripple-upgraded" style="--mdc-ripple-fg-size: 158px; --mdc-ripple-fg-scale: 1.7754826332433935; --mdc-ripple-fg-translate-start: 80px, -39px; --mdc-ripple-fg-translate-end: 53.333343505859375px, -51px;"><span class="mdc-text-field__ripple"></span> 
  {#if !from && !to}
 	<span class="mdc-floating-label" style="">filtrovat dle data úmrtí</span>    
  {:else}  
 	<span class="mdc-floating-label mdc-floating-label--float-above" style="">filtrovat dle data úmrtí</span>
  {/if}
 <Flatpickr
    bind:options={flatpickrOptionsRange}
    class="form-control datepicker bg-white"
    defaultDate=""
    placeholder="filtrovat dle data úmrtí"
    on:change={event => handleChange(event)} 
    element="#my-picker"
    > 
    	  <div class="flatpickr" id="my-picker">
		<input type="text" placeholder="filtrovat dle data úmrtí" data-input class="mdc-text-field__input" />
		<div style="position:relative;top:-50px; left:222px;"><button data-clear on:click="{()=>reset()}">X</button></div>
	  </div>  
 </Flatpickr>
 <div class="mdc-line-ripple" style="transform-origin: 143px center 0px;"></div></label> 

</div>

<style>
.range-picker { margin-top:0px; }
.range-picker input { height:50px !important; }
</style>
