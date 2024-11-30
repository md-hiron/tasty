// Configuration
const siteUrl            = window.location.origin;
const apiEndpoint        = siteUrl + '/tasty/wp-json/tasty/v1/get-tasty-posts';
const frameSelector      = '.frame';
const likeButtonSelector = '#like';
const hateButtonSelector = '#hate';

// State variables
let data = [];
let current = null;
let likeText = null;
let startX = 0;
let startY = 0;
let moveX = 0;
let moveY = 0;
let cardIndex = 0;
const storeAction = { likes: [], dislikes: [] };

// Initialize
function initSwipeCards() {
    const frame = document.querySelector(frameSelector);
    const likeButton = document.querySelector(likeButtonSelector);
    const hateButton = document.querySelector(hateButtonSelector);

    fetchInitialData()
        .then(fetchedData => {
            data = fetchedData;
            data.forEach(item => appendCard(item, frame));

            // Set the initial card
            current = frame.querySelector('.card:last-child');
            likeText = current?.children[0];
            initCard(current, frame);
        })
        .catch(error => console.error('Error loading initial data:', error));

    likeButton.addEventListener('click', () => likeHandler(frame));
    hateButton.addEventListener('click', () => hateHandler(frame));
}

// Fetch initial data
async function fetchInitialData() {
    try {
        const response = await fetch(apiEndpoint);
        return response.ok ? await response.json() : [];
    } catch (error) {
        console.error('Error fetching data:', error);
        return [];
    }
}

// Append a card to the frame
function appendCard(cardData, frame) {
    const firstCard = frame.children[0];
    const newCard = document.createElement('div');
    newCard.setAttribute('data-post-id', cardData.id);
    newCard.className = 'card';
    newCard.style.backgroundImage = `url(${cardData.featured_image})`;
    newCard.innerHTML = `
        <div class="is-like"></div>
        <div class="bottom">
            <div class="title">
                <span>${cardData.title}</span>
            </div>
        </div>
    `;
    if (firstCard) frame.insertBefore(newCard, firstCard);
    else frame.appendChild(newCard);
}

// Initialize card for interaction
function initCard(card, frame) {
    if (card) {
        card.addEventListener('pointerdown', (e) => onPointerDown(e, frame));
    }
}

// Pointer down event handler
function onPointerDown({ clientX, clientY }, frame) {
    startX = clientX;
    startY = clientY;
    current.addEventListener('pointermove', onPointerMove);
    current.addEventListener('pointerup', () => onPointerUp(frame));
    current.addEventListener('pointerleave', () => onPointerUp(frame));
}

// Pointer move event handler
function onPointerMove({ clientX, clientY }) {
    moveX = clientX - startX;
    moveY = clientY - startY;
    setTransform(moveX, moveY, moveX / innerWidth * 50);
}

// Pointer up event handler
function onPointerUp(frame) {
    current.removeEventListener('pointermove', onPointerMove);
    current.removeEventListener('pointerup', () => onPointerUp(frame));
    current.removeEventListener('pointerleave', () => onPointerUp(frame));

    if (Math.abs(moveX) > frame.clientWidth / 7) {
        current.removeEventListener('pointerdown', (e) => onPointerDown(e, frame));
        completeAction(frame);
    } else {
        cancelAction();
    }
}

// Set card transformation
function setTransform(x, y, deg, duration = 100, isButtonClick = false) {
    const isMobile = innerWidth <= 768;
    current.style.transform = `translate3d(${x}px, ${y}px, 0) rotate(${deg}deg)`;
    likeText.style.opacity = isMobile && isButtonClick ? 1 : Math.abs((x / innerWidth) * 2.1);
    likeText.className = `is-like ${x > 0 ? 'like' : 'nope'}`;
    current.style.transition = isMobile && isButtonClick ? `transform 300ms` : `transform ${duration}ms`;
}

// Complete an action
async function completeAction(frame) {
    const flyMultiplier = innerWidth <= 768 ? 1.5 : 1.3;
    const flyX = (Math.abs(moveX) / moveX) * innerWidth * flyMultiplier;
    const flyY = (moveY / moveX) * flyX;

    setTransform(flyX, flyY, flyX / innerWidth * 50, innerWidth);

    if (current) {
        if (moveX > 0) handleLike(data[cardIndex]);
        else handleDislike(data[cardIndex]);
    }

    const prev = current;
    const next = current.previousElementSibling;

    if (next) {
        initCard(next, frame);
        current = next;
        likeText = current.children[0];
    } else {
        current = null;
        likeText = null;
    }

    setTimeout(() => {
        frame.removeChild(prev);
        if (!current) displayNoMoreCardsMessage(frame);
    }, innerWidth);

    // Fetch more cards if needed
    if (data.length - cardIndex <= 3) {
        const newData = await fetchInitialData();
        newData.forEach(item => appendCard(item, frame));
        data.push(...newData);
    }
}

// Cancel an action
function cancelAction() {
    setTransform(0, 0, 0);
    setTimeout(() => (current.style.transition = ''), 100);
}

// Handle like
function handleLike(cardData) {
    console.log('Liked:', cardData);
    storeAction.likes.push(cardData.name);
}

// Handle dislike
function handleDislike(cardData) {
    console.log('Disliked:', cardData);
    storeAction.dislikes.push(cardData.name);
}

// Like button handler
function likeHandler(frame) {
    likeText.style.opacity = 1;
    likeText.className = 'is-like like';
    setTimeout(() => {
        moveX = 1;
        moveY = 0;
        completeAction(frame);
    }, 200);
}

// Hate button handler
function hateHandler(frame) {
    likeText.style.opacity = 1;
    likeText.className = 'is-like nope';
    setTimeout(() => {
        moveX = -1;
        moveY = 0;
        completeAction(frame);
    }, 150);
}

// Display a message when no more cards are available
function displayNoMoreCardsMessage(frame) {
    const message = document.createElement('div');
    message.className = 'card no-more-cards';
    message.innerHTML = '<div class="no-more-item">No more cards available</div>';
    frame.appendChild(message);
}

// Initialize the swipe cards system
initSwipeCards();
