document.addEventListener("DOMContentLoaded", function() {
    let todayDiv = document.querySelector(".today");

    if (todayDiv) {
        window.scrollTo({
            top: todayDiv.offsetTop - 50,
            behavior: "smooth" //плавность прокрутки
        });
    }
});
