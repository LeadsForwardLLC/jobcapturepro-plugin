
function jobcaptureproChangeImage(event, galleryId, direction) {
    // Prevent redirection when the user only wants to change the image
    event.preventDefault();

    // Get gallery elements
    const gallery = document.getElementById(galleryId);
    const images = gallery.querySelectorAll(".gallery-image");
    const dots = gallery.querySelectorAll(".gallery-dot");

    // Find current active image
    let currentIndex = 0;
    for (let i = 0; i < images.length; i++) {
        if (images[i].classList.contains("active")) {
            currentIndex = i;
            break;
        }
    }

    // Remove active class from current image and dot
    images[currentIndex].classList.remove("active");
    if (dots.length) dots[currentIndex].classList.remove("active");

    // Calculate new index
    let newIndex;
    if (direction === "next") {
        newIndex = (currentIndex + 1) % images.length;
    } else {
        newIndex = (currentIndex - 1 + images.length) % images.length;
    }

    // Add active class to new image and dot
    images[newIndex].classList.add("active");
    if (dots.length) dots[newIndex].classList.add("active");
}

function jobcaptureproShowImage(event, galleryId, index) {
    // Prevent redirection when the user only wants to see an image
    event.preventDefault();

    // Get gallery elements
    const gallery = document.getElementById(galleryId);
    const images = gallery.querySelectorAll(".gallery-image");
    const dots = gallery.querySelectorAll(".gallery-dot");

    // Remove active class from all images and dots
    for (let i = 0; i < images.length; i++) {
        images[i].classList.remove("active");
        if (dots.length) dots[i].classList.remove("active");
    }

    // Add active class to selected image and dot
    images[index].classList.add("active");
    if (dots.length) dots[index].classList.add("active");
}