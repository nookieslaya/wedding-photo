/*
JS INTERVIEW PACK (10 tematów)
Szybka ściąga: pytanie + odpowiedź + mini przykład.
*/

// 1) const vs let
// P: Kiedy const, a kiedy let?
// O: const gdy referencja ma się nie zmieniać, let gdy wartość będzie reassigned.
const apiBase = "/wp-json";
let currentPage = 1;
currentPage = 2;

// 2) Scope i hoisting
// P: Co to scope?
// O: Zmienna jest dostępna tylko w swoim zasięgu (blok/funkcja/moduł).
if (true) {
  const local = "tylko w bloku";
  console.log(local);
}
// console.log(local); // ReferenceError

// 3) Arrow function vs function
// P: Różnica?
// O: Arrow nie ma własnego `this`; dziedziczy `this` z otoczenia.
const user = {
  name: "Radek",
  classic() {
    return this.name;
  },
  arrow: () => this?.name, // zwykle undefined w tym kontekście
};

// 4) Closures
// P: Co to closure?
// O: Funkcja pamięta zmienne z miejsca, gdzie została utworzona.
function makeCounter() {
  let count = 0;
  return () => ++count;
}
const counter = makeCounter();
counter(); // 1
counter(); // 2

// 5) Promise + async/await
// P: Jak obsłużyć async kod?
// O: async/await + try/catch.
async function loadPost(id) {
  try {
    const res = await fetch(`/wp-json/wp/v2/posts/${id}`);
    if (!res.ok) throw new Error("HTTP error");
    return await res.json();
  } catch (err) {
    console.error("Load failed:", err);
    return null;
  }
}

// 6) map/filter/find/reduce
// P: Kiedy używać?
// O: map (transform), filter (wybór), find (pierwszy), reduce (agregacja).
const prices = [10, 30, 50];
const withVat = prices.map((p) => p * 1.23);
const expensive = prices.filter((p) => p > 20);
const firstBig = prices.find((p) => p > 20);
const sum = prices.reduce((acc, p) => acc + p, 0);

// 7) Event delegation
// P: Po co delegation?
// O: Jeden listener dla wielu elementów (także dodanych dynamicznie).
document.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-remove]");
  if (!btn) return;
  btn.closest("li")?.remove();
});

// 8) Debounce
// P: Po co debounce?
// O: Ogranicza liczbę wywołań (np. search input).
function debounce(fn, delay = 300) {
  let t;
  return (...args) => {
    clearTimeout(t);
    t = setTimeout(() => fn(...args), delay);
  };
}
const onSearch = debounce((q) => console.log("Search:", q), 250);

// 9) Moduły
// P: Po co import/export?
// O: Lepszy podział kodu i testowalność.
// math.js -> export const add = (a, b) => a + b;
// app.js  -> import { add } from "./math.js";

// 10) XSS basics (frontend security)
// P: Jak bezpiecznie renderować input usera?
// O: Nie wkładaj raw user input przez innerHTML, używaj textContent.
function safeRender(el, userText) {
  el.textContent = userText; // bezpieczniejsze niż innerHTML
}

/*
Jak to powiedzieć na rozmowie (krótko):
1. "W async kodzie używam async/await i zawsze mam try/catch."
2. "Na listach najczęściej map/filter/find/reduce zamiast pętli imperatywnych."
3. "W DOM preferuję event delegation i debounce przy inputach."
4. "Pilnuję bezpieczeństwa: nie renderuję surowego HTML z inputu użytkownika."
*/
