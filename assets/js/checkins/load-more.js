document.addEventListener("DOMContentLoaded", function () {
    const loadMoreBtn = document.getElementById("load-more-checkins-btn");

    if (!loadMoreBtn) return;

    loadMoreBtn.addEventListener("click", loadNextCheckinsPage);


    function loadNextCheckinsPage() {
        console.log('Yaay!')
    }
});