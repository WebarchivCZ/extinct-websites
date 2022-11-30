import { writable } from 'svelte/store';


export const api = writable("/api/v2/");
export const apiCheck = writable("/autocheck");
//export const db = writable("webarchiv");
export const db = writable("");
