<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review Page</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: hsl(248, 9%, 91.5%);
            height: 100svh;
            overflow-y: hidden;
            position: relative;
            display: flex;
            align-items: center;
        }

        .container {
            width: min(350px, 73%);
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 0.57rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.23);
            min-height: 470px;
            display: flex;
            justify-content: space-around;
            flex-direction: column;

            .review-card {
                display: flex;
                flex-direction: column;
                gap: 23px;

                .business-info {
                    display: flex;
                    align-items: center;
                    gap: 15px;

                    .business-logo {
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                    }

                    .business-details {
                        h2 {
                            margin: 0;
                            font-size: 1.5rem;
                        }

                        p {
                            margin: 5px 0 0;
                            color: #666;
                            font-size: 0.9rem;
                            line-height: 1.4;
                        }
                    }
                }

                .rating {
                    display: flex;
                    justify-content: center;
                    gap: 5px;
                    flex-direction: row-reverse;
                }

                .star {
                    font-size: 2.3rem;
                    position: relative;
                    cursor: pointer;
                    color: #c9c9c9;
                }

                .star:before {
                    content: '★';
                    position: absolute;
                    opacity: 0;
                    transition: color 0.7s ease, opacity 0.7s ease;
                }

                .star:hover:before,
                .star:hover~.star:before {
                    opacity: 1;
                }

                .star:hover,
                .star:hover~.star {
                    color: orange;
                }

                .reviewer {
                    display: grid;
                    gap: 0.67rem;
                }

                .name-input,
                textarea {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    font-size: 1rem;
                    box-sizing: border-box;
                }

                textarea {
                    resize: none;
                    min-height: 150px;
                }

                .button-group {
                    display: grid;
                    gap: 10px;
                    grid-auto-flow: column;
                    grid-template-columns: 1fr 1fr;
                    position: relative;
                    transform: translateY(min(1.7rem, 7.3%));

                    .photo-button,
                    .post-button {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.57rem;
                        padding: 10px 15px;
                        border: none;
                        border-radius: 5px;
                        font-size: 1rem;
                        cursor: pointer;
                        height: max-content;
                        transition: background-color 0.3s ease, color 0.3s ease;

                        .material-icons {
                            font-size: 1.5rem;
                            vertical-align: middle;
                            padding: 0 0 2.3px 0;
                        }
                    }

                    .photo-button {
                        background-color: hsl(0, 0%, 85%);
                        color: #333;
                        font-weight: bold;

                        &:hover {
                            background-color: hsl(0, 0%, 89%);
                        }
                    }

                    .post-button {
                        background-color: #1a73e8;
                        color: white;
                        font-weight: bold;

                        &:hover {
                            background-color: #0b5bdc;
                        }
                    }
                }
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;

                .review-card {
                    .business-info {
                        .business-details {
                            h2 {
                                font-size: 1.3rem;
                            }

                            p {
                                font-size: 0.85rem;
                            }
                        }
                    }

                    .rating .star {
                        font-size: 2rem;
                    }

                    .button-group button {
                        font-size: 0.95rem;
                    }
                }
            }
        }

        @media (max-width: 480px) {
            .container .review-card {
                .business-info {
                    flex-direction: row;
                    align-items: center;
                    gap: 13px;

                    .business-logo {
                        width: 40px;
                        height: 40px;
                    }
                }

                .container .review-card {
                    transform: (max(-1.5rem, -7.3%)) !important;
                }

                .rating .star {
                    font-size: 1.8rem;
                }

                .button-group button {
                    font-size: 0.9rem;
                    padding: 8px 10px;
                }
            }
        }

        @media (max-width: 385px) {
            .container .review-card {
                .business-info {
                    transform: translateY(0);
                }
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="review-card">
            <div class="business-info">
                <img src="{{ asset('images/user (2).png') }}" alt="Default Profile Picture" class="business-logo">
                <div class="business-details">
                    <h2>{{ $location->title }}</h2>
                    <p>{{ $location->formatted_address ?: '' }}</p>
                </div>
            </div>
            <div class="rating">
                <span class="star">☆</span>
                <span class="star">☆</span>
                <span class="star">☆</span>
                <span class="star">☆</span>
                <span class="star">☆</span>
            </div>
            <div class="reviewer">
                <input type="text" placeholder="Your Name" class="name-input">
                <textarea placeholder="Share your impressions of this place"></textarea>
            </div>
            <div class="button-group">
                <button class="photo-button">
                    <span class="material-icons">add_a_photo</span>
                    Add Photos
                </button>
                <button class="post-button">
                    <span class="material-icons">send</span>
                    Post
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stars = document.querySelectorAll('.star');
            const reviewForm = document.querySelector('.review-card');
            const nameInput = document.querySelector('.name-input');
            const commentTextarea = document.querySelector('textarea');
            const postButton = document.querySelector('.post-button');
            let selectedRating = 0;

            const locationId = '{{ $location->id }}';
            const newReviewUri = '{{ $location->new_review_uri }}';

            stars.forEach((star, index) => {
                const ratingValue = 5 - index;

                star.addEventListener('click', () => {
                    selectedRating = ratingValue;
                    updateStars(selectedRating);

                    if (selectedRating >= 4 && newReviewUri) {
                        window.open(newReviewUri, '_blank');
                        return;
                    }
                });
            });

            postButton.addEventListener('click', async () => {
                if (!selectedRating) {
                    alert('Please select a rating');
                    return;
                }

                if (selectedRating >= 4) {
                    window.open(newReviewUri, '_blank');
                    return;
                }

                if (!nameInput.value.trim()) {
                    alert('Please enter your name');
                    return;
                }

                if (!commentTextarea.value.trim()) {
                    alert('Please share your impressions');
                    return;
                }

                const reviewData = {
                    reviewer_name: nameInput.value.trim(),
                    comment: commentTextarea.value.trim(),
                    star_rating: getStarRatingEnum(selectedRating),
                    create_time: new Date().toISOString(),
                    location_id: locationId
                };

                try {
                    const response = await fetch('/api/reviews', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(reviewData)
                    });

                    const data = await response.json();

                    if (response.ok) {
                        alert('Thank you for your review!');
                        resetForm();
                    } else {
                        throw new Error(data.error || 'Failed to submit review');
                    }
                } catch (error) {
                    console.error('Error submitting review:', error);
                    alert(error.message || 'There was an error submitting your review. Please try again.');
                }
            });

            function updateStars(rating) {
                stars.forEach((star, index) => {
                    const starValue = 5 - index;
                    if (starValue <= rating) {
                        star.style.color = 'orange';
                        star.style.opacity = '1';
                        star.innerHTML = '★';
                    } else {
                        star.style.color = '#c9c9c9';
                        star.style.opacity = '1';
                        star.innerHTML = '☆';
                    }
                });
            }

            function getStarRatingEnum(rating) {
                const enumMap = {
                    1: 'ONE',
                    2: 'TWO',
                    3: 'THREE',
                    4: 'FOUR',
                    5: 'FIVE'
                };
                return enumMap[rating];
            }

            function resetForm() {
                selectedRating = 0;
                nameInput.value = '';
                commentTextarea.value = '';
                updateStars(0);
            }
        });
    </script>
</body>

</html>