document.addEventListener("click", (e) => {
	const btn = e.target.closest(".discogs-list-id-copy");
	if (!btn) return;

	const id = btn.dataset.id;
	if (!id) return;

	navigator.clipboard.writeText(id).then(() => {
	const originalText = btn.textContent;
	btn.innerHTML = "✔ Copied!";
	btn.disabled = true;
	setTimeout(() => {
		btn.innerHTML = originalText;
		btn.disabled = false;
	}, 1500);
	}).catch(console.error);
});