<script>
  import { Link } from "svelte-routing";

  export let to = "";
  export let url;
  

  function getProps({ location, href, isPartiallyCurrent, isCurrent }) {
    const isActive = href === "/" ? isCurrent : isPartiallyCurrent || isCurrent;

    // The object returned here is spread on the anchor element's attributes
    if (isActive) {
      return { class: "active" };
    }
    return {};
  }
</script>


{#if url && url==to}
	  <slot />
{:else}
<Link to="{to}" getProps="{getProps}">
  <slot />
</Link>
{/if}
&nbsp;&nbsp;|&nbsp;&nbsp;


