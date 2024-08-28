import { writable } from 'svelte/store';

export const api = writable("http://10.3.0.24/api/v2/");
export const apiCheck = writable("http://10.3.0.24/autocheck");
export const apiFeeder = writable("http://10.3.0.24/api/urlFeeder/");
//export const db = writable("webarchiv");
//export const db = writable("test2");
export const db = writable("test2");
