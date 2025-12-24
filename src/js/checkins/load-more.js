document.addEventListener("DOMContentLoaded", function () {
    // Current page tracker
    let currentPage = 1;

    // Elements
    const loadMoreBtn = document.getElementById("load-more-checkins-btn");
    const checkinsGrid = document.getElementById("checkins-grid");

    // Return early if elements not found
    if (!loadMoreBtn || !checkinsGrid) return;

    // Event listener for Load More button
    loadMoreBtn.addEventListener("click", loadNextCheckinsPage);

    // Load next page of check-ins
    function loadNextCheckinsPage() {
        // Change button to loading state
        changeButtonState('loading');

        // Fetch next page of check-ins
        fetch(`${jobcaptureproLoadMoreData.baseApiUrl}/checkins?page=${currentPage + 1}&companyId=${jobcaptureproLoadMoreData.companyId}`)
            .then(response => response.json())
            .then(data => {
                const checkins = data.checkins;

                // Append new check-ins if available
                if (checkins && checkins.length > 0) {
                    // Append new check-ins to the grid
                    checkinsGrid.insertAdjacentHTML('beforeend', renderCheckins(checkins));

                    // Re-arrange items after adding new ones
                    if (window.rearrangeItems) {
                        window.rearrangeItems();
                    }
                } else {
                    loadMoreBtn.classList.add('jcp:hidden');
                }

                // If no more pages, hide the button
                if (data.hasNext) {
                    // Increment current page
                    currentPage++;

                    // Return button to normal state
                    changeButtonState('normal');
                } else {
                    loadMoreBtn.classList.remove('jcp:block');
                    loadMoreBtn.classList.add('jcp:hidden');
                }
            })
            .catch(error => {
                console.error('Error fetching check-ins:', error);
            });
    }

    // Render a single check-in card
    function renderCheckinCard(checkin) {
        // Parse address (assuming format: "Street, City, State, ZIP, Country")
        const addressParts = checkin.address.split(',');

        // Get city (2nd part) and state (3rd part)
        const city = (addressParts[1] || '').trim();
        const state = (addressParts[2] || '').trim();

        // Shorten state abbreviation if needed (e.g., "California" → "CA")
        const stateAbbr = state.length > 2 ? state.substring(0, 2) : state;

        // Process date
        const timestamp = new Date(checkin.createdAt);
        const formattedDate = timestamp.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Helper function to escape HTML
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        // Helper function to convert newlines to <br>
        const nl2br = (text) => {
            return escapeHtml(text).replace(/\n/g, '<br>');
        };

        // Generate image gallery HTML (if available)
        let imageGalleryHtml = '';
        if (checkin.imageUrls && Array.isArray(checkin.imageUrls) && checkin.imageUrls.length > 0) {
            // You'll need to implement renderImageGallery function based on your Template::render_template logic
            imageGalleryHtml = renderImageGallery({
                imageUrls: checkin.imageUrls,
                imageCount: checkin.imageUrls.length,
                showArrows: checkin.imageUrls.length > 1,
                galleryId: `checkin-gallery-${checkin.id}`
            });
        }

        // Generate user info HTML (if available)
        let userInfoHtml = '';
        if (checkin.assignedUser) {
            const user = checkin.assignedUser;

            let profileImageHtml = '';
            if (user.profileImageUrl) {
                profileImageHtml = `
                <div class="jobcapturepro-user-image">
                    <img src="${escapeHtml(user.profileImageUrl)}" alt="User profile">
                </div>
            `;
            }

            let userNameHtml = '';
            if (user.name) {
                userNameHtml = `
                <div class="jobcapturepro-user-name">
                    <p>${escapeHtml(user.name)}</p>
                </div>
            `;
            }

            const starsHtml = `
            <div class="jobcapturepro-job-reviews">
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                    </svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                    </svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                    </svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                    </svg>
                </span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M341.5 45.1C337.4 37.1 329.1 32 320.1 32C311.1 32 302.8 37.1 298.7 45.1L225.1 189.3L65.2 214.7C56.3 216.1 48.9 222.4 46.1 231C43.3 239.6 45.6 249 51.9 255.4L166.3 369.9L141.1 529.8C139.7 538.7 143.4 547.7 150.7 553C158 558.3 167.6 559.1 175.7 555L320.1 481.6L464.4 555C472.4 559.1 482.1 558.3 489.4 553C496.7 547.7 500.4 538.8 499 529.8L473.7 369.9L588.1 255.4C594.5 249 596.7 239.6 593.9 231C591.1 222.4 583.8 216.1 574.8 214.7L415 189.3L341.5 45.1z"></path>
                    </svg>
                </span>
            </div>
        `;

            userInfoHtml = `
            <div class="jobcapturepro-checkin-user">
                ${profileImageHtml}
                ${userNameHtml}
                ${starsHtml}
            </div>
        `;
        }

        return `
        <div class="jobcapturepro-checkin-card" style="text-decoration: none; color: inherit;">
            ${imageGalleryHtml}
            ${userInfoHtml}
            
            <!-- Description -->
            <div class="jobcapturepro-checkin-description">
                <p>${nl2br(checkin.description)}</p>
            </div>

            <!-- Date - Simplified to only show the formatted date -->
            <div class="jobcapturepro-checkin-date">
                <p class="date-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                        <path d="M224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64zM160 304L160 336C160 344.8 167.2 352 176 352L208 352C216.8 352 224 344.8 224 336L224 304C224 295.2 216.8 288 208 288L176 288C167.2 288 160 295.2 160 304zM288 304L288 336C288 344.8 295.2 352 304 352L336 352C344.8 352 352 344.8 352 336L352 304C352 295.2 344.8 288 336 288L304 288C295.2 288 288 295.2 288 304zM432 288C423.2 288 416 295.2 416 304L416 336C416 344.8 423.2 352 432 352L464 352C472.8 352 480 344.8 480 336L480 304C480 295.2 472.8 288 464 288L432 288zM160 432L160 464C160 472.8 167.2 480 176 480L208 480C216.8 480 224 472.8 224 464L224 432C224 423.2 216.8 416 208 416L176 416C167.2 416 160 423.2 160 432zM304 416C295.2 416 288 423.2 288 432L288 464C288 472.8 295.2 480 304 480L336 480C344.8 480 352 472.8 352 464L352 432C352 423.2 344.8 416 336 416L304 416zM416 432L416 464C416 472.8 423.2 480 432 480L464 480C472.8 480 480 472.8 480 464L480 432C480 423.2 472.8 416 464 416L432 416C423.2 416 416 423.2 416 432z" />
                    </svg>${escapeHtml(formattedDate)}
                </p>
            </div>

            <!-- Address - Extract city and state only -->
            <div class="jobcapturepro-checkin-address">
                <p><strong>Near</strong> ${escapeHtml(city + ', ' + stateAbbr)}</p>
            </div>
        </div>
        `;
    }

    // Render image gallery
    function renderImageGallery({ imageUrls, imageCount, showArrows, galleryId }) {
        // Helper function to escape HTML
        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        // Helper function to escape for JavaScript
        const escapeJs = (text) => {
            return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
        };

        // Generate image elements
        const imagesHtml = imageUrls.map((imageUrl, index) => {
            const activeClass = index === 0 ? ' active' : '';
            return `
            <div class="gallery-image${activeClass}" data-index="${index}">
                <img src="${escapeHtml(imageUrl)}" alt="${escapeHtml('Checkin image ' + (index + 1))}">
            </div>
        `;
        }).join('');

        // Generate navigation arrows and dots if needed
        let navigationHtml = '';
        if (showArrows) {
            // Generate dots
            const dotsHtml = Array.from({ length: imageCount }, (_, i) => {
                const activeClass = i === 0 ? ' active' : '';
                return `<span class="gallery-dot${activeClass}" onclick="jobcaptureproShowImage('${escapeJs(galleryId)}', ${i})"></span>`;
            }).join('');

            navigationHtml = `
            <div class="gallery-nav gallery-prev" onclick="jobcaptureproChangeImage('${escapeJs(galleryId)}', 'prev')">&#10094;</div>
            <div class="gallery-nav gallery-next" onclick="jobcaptureproChangeImage('${escapeJs(galleryId)}', 'next')">&#10095;</div>
            <div class="gallery-dots">
                ${dotsHtml}
            </div>
        `;
        }

        return `
        <div class="jobcapturepro-checkin-image" id="${escapeHtml(galleryId)}">
            ${imagesHtml}
            ${navigationHtml}
        </div>
    `;
    }

    // Usage with array of checkins:
    function renderCheckins(checkinsArray) {
        return checkinsArray.map(checkin => renderCheckinCard(checkin)).join('');
    }

    // Button state management
    function changeButtonState(state = 'normal') {
        // Button spinner SVG element
        const buttonSpinner = loadMoreBtn.children[1];

        switch (state) {
            case 'loading':
                // Enable/disable button
                loadMoreBtn.disabled = true;

                // Show spinner
                buttonSpinner.classList.remove('jcp:hidden');

                // Loading state styles
                loadMoreBtn.classList.remove('jcp:bg-accent', 'jcp:text-white', 'hover:jcp:bg-red-600', 'jcp:cursor-pointer', 'jcp:opacity-100');
                loadMoreBtn.classList.add('jcp:bg-gray-500', 'jcp:text-gray-200', 'jcp:cursor-not-allowed', 'jcp:opacity-60');
                break;
            case 'normal':
                // Enable/disable button
                loadMoreBtn.disabled = false;

                // Hide spinner
                buttonSpinner.classList.add('jcp:hidden');

                // Normal state styles
                loadMoreBtn.classList.remove('jcp:bg-gray-500', 'jcp:text-gray-200', 'jcp:cursor-not-allowed', 'jcp:opacity-60');
                loadMoreBtn.classList.add('jcp:bg-accent', 'jcp:text-white', 'hover:jcp:bg-red-600', 'jcp:cursor-pointer', 'jcp:opacity-100');
                break;
            default:
                break;
        }
    }
});
