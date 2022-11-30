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

const flatpickrOptionsRange = {
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

</script>

<div class="range-picker" label="Range picker">
  <Flatpickr
    options={flatpickrOptionsRange}
    class="form-control datepicker bg-white"
    defaultDate=""
    placeholder="filtrovat dle data úmrtí"
    on:change={event => handleChange(event)} />
</div>

<style>
.range-picker input { height:95px !important; }
</style>
