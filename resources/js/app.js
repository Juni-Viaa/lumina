import './bootstrap';
import { marked } from "marked";
import DOMPurify from "dompurify";

import Alpine from 'alpinejs'

window.marked = marked;
window.DOMPurify = DOMPurify;

window.Alpine = Alpine
Alpine.start()