// Configuration
const siteUrl            = window.location.origin;
const getPostEndPoint    = siteUrl + '/tasty/wp-json/tasty/v1/get-tasty-posts';
const saveChoiceEndPoint = siteUrl + '/tasty/wp-json/tasty/v1/save_choices';
const frameSelector      = '.frame';
const likeButtonSelector = '#like';
const hateButtonSelector = '#hate';

// Global variables
const frame = document.querySelector(frameSelector); // Defined globally
let data = [];
let current = null;
let likeText = null;
let startX = 0;
let startY = 0;
let moveX = 0;
let moveY = 0;
const swipedIds = [];
const loadedIds = [];

// Initialize the system
function initSwipeCards() {
    const likeButton = document.querySelector(likeButtonSelector);
    const hateButton = document.querySelector(hateButtonSelector);

    fetchInitialData()
        .then(fetchedData => {
            data = fetchedData;
            data.forEach(item => {
                appendCard(item)
                loadedIds.push(item.id);
            });

            // Set the initial card
            current = frame.querySelector('.card:last-child');
            likeText = current?.children[0];
            attachCardEventListeners(current);
        })
        .catch(error => console.error('Error loading initial data:', error));

    likeButton.addEventListener('click', () => likeHandler('like'));
    hateButton.addEventListener('click', () => hateHandler('dislike'));
}



// Fetch initial data
async function fetchInitialData() {
    try {
        const response = await fetch(getPostEndPoint, {
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce 
            },
            credentials: 'include'
        });
        const fetchData =  response.ok ? await response.json() : [];

        if( fetchData.length === 0 ){
            appendPlaceholderCard( 'Für den aktuellen Benutzer sind keine Beiträge verfügbar' );
        }

        return fetchData;

    } catch (error) {
        console.error('Error fetching data:', error);
        return [];
    }
}

// Append a card to the frame
function appendCard(cardData) {
    const firstCard = frame.children[0];
    const newCard = document.createElement('div');
    newCard.className = 'card';
    newCard.setAttribute('data-post-id', cardData.id);
    newCard.style.backgroundImage = `url(${cardData.featured_image})`;
    newCard.innerHTML = `
        <div class="is-like"></div>
        <div class="bottom">
            <div class="title">
                <span>${cardData.title}</span>
            </div>
            ${ cardData.addition_info ? `<h3 class="tasty-additional">${cardData.addition_info}</h3>` : '' }
        </div>
    `;
    if (firstCard) frame.insertBefore(newCard, firstCard);
    else frame.appendChild(newCard);
}

// Attach event listeners to a card
function attachCardEventListeners(card) {
    if (card) {
        card.addEventListener('pointerdown', onPointerDown);
    }
}

// Pointer down event handler
function onPointerDown({ clientX, clientY }) {
    startX = clientX;
    startY = clientY;

    current.addEventListener('pointermove', onPointerMove);
    current.addEventListener('pointerup', onPointerUp);
    current.addEventListener('pointerleave', onPointerUp);
}

// Pointer move event handler
function onPointerMove({ clientX, clientY }) {
    moveX = clientX - startX;
    moveY = clientY - startY;
    setTransform(moveX, moveY, moveX / innerWidth * 50);
}

// Pointer up event handler
function onPointerUp() {
    current.removeEventListener('pointermove', onPointerMove);
    current.removeEventListener('pointerup', onPointerUp);
    current.removeEventListener('pointerleave', onPointerUp);

    if (Math.abs(moveX) > frame.clientWidth / 7) {
        // Swipe threshold reached, complete the action
        current.removeEventListener('pointerdown', onPointerDown);
        const action = moveX > 0 ? 'like' : 'dislike';
        completeAction( action );
    } else {
        // Cancel the swipe if threshold not reached
        cancelAction();
    }
}

// Set card transformation
function setTransform(x, y, deg, duration, isButtonClick = false) {
    current.style.transform = `translate3d(${x}px, ${y}px, 0) rotate(${deg}deg)`;
    likeText.style.opacity = innerWidth <= 768 && isButtonClick ? 1 : Math.abs(x / innerWidth) * 2.1;
    likeText.className = `is-like ${x > 0 ? 'like' : 'nope'}`;

    if (duration) current.style.transition =  innerWidth <= 768 && isButtonClick ? `transform 300ms` : `transform ${duration}ms`
}

// Complete the swipe action
function completeAction( action, isButtonClick = false ) {
    const postId = data[data.length - frame.children.length].id;
    const flyMultiplier = innerWidth <= 768 ? 1.5 : 1.7;
    const flyX = (Math.abs(moveX) / moveX) * innerWidth * flyMultiplier;
    const flyY = (moveY / moveX) * flyX;

    setTransform(flyX, flyY, flyX / innerWidth * 50, innerWidth, isButtonClick);

    swipedIds.push(postId)

    const prev = current;
    const next = current.previousElementSibling;


    
    if( swipedIds.length === 3 ){
        console.log(loadedIds);
        fetchMoreData(swipedIds)
        swipedIds.length = 0;

    }

    saveChoiceToDatabase( postId, action );

    if (next) {
        attachCardEventListeners(next);
        current = next;
        likeText = current.children[0];
    } else {
        current = null;
        likeText = null;
    }

    setTimeout(() => {
        frame.removeChild(prev);
        if (!current) displayNoMoreCardsMessage();
    }, 300);
}

//save user choice in database
async function saveChoiceToDatabase( postID, action ){
    console.log({ post_id: postID, choice: action });
    console.log('nonce '+wpApiSettings.nonce);
    try{
        const response = await fetch( saveChoiceEndPoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce 
            },
            credentials: 'include',
            body: JSON.stringify({ post_id: postID, choice: action })
        } );

        if( !response.ok ){
            throw new Error( 'Failed to save user choice to database' );
        }

        const result = await response.json();
        console.log(result);
    }catch( error ){
        throw new Error( error );
    }
}

async function fetchMoreData(swipedIds){
   //appendLoadingCard();
    try{
        const response = await fetch( getPostEndPoint+`?swiped_ids=${swipedIds}&loaded_ids=${loadedIds}`, {
            'method': 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce
            },
            credentials: 'include'
        } );

        if( ! response.ok ){
            throw new Error('Failded to fetch more posts');
        }

        const newPosts = await response.json();

        newPosts.forEach( item => {
            loadedIds.push(item.id);
            appendCard(item);
        } );
        data.push(...newPosts);

       
        
    }catch( error ){
        throw new Error( error );
    }
}

// Cancel the swipe action
function cancelAction() {
    setTransform(0, 0, 0);
    setTimeout(() => {
        current.style.transition = '';
    }, 100);
}

// Like button handler
function likeHandler(action) {
    likeText.style.opacity = 1
    likeText.className = 'is-like like'
    setTimeout( function(){
        moveX = 1;
        moveY = 0;
        completeAction( action, true ); 
    }, 300)
}

// Hate button handler
function hateHandler(action) {

    likeText.style.opacity = 1
    likeText.className = 'is-like nope'
    setTimeout( function(){
        moveX = -1
        moveY = 0
        completeAction( action, true ); 
    }, 300)

}

// Display "No More Cards" message
function displayNoMoreCardsMessage() {
    const message = document.createElement('div');
    message.className = 'card no-more-cards';
    message.innerHTML = '<div class="no-more-item">Keine weiteren Karten vorhanden</div>';
    frame.appendChild(message);
}

// Append a placeholder card to the frame
function appendPlaceholderCard(message) {
    const placeholderCard = document.createElement('div');
    placeholderCard.className = 'card placeholder-card';
    placeholderCard.innerHTML = `
        <div class="no-more-item">${message}</div>
    `;
    frame.appendChild(placeholderCard);
    setButtonsDisabled(true);
}

// Append a loading card to the frame
function appendLoadingCard() {
    const loadingCard = document.createElement('div');
    loadingCard.className = 'card loading-card';
    loadingCard.innerHTML = '<div class="no-more-item">Loading...</div>';
    frame.insertBefore(loadingCard, frame.firstChild);
}

// Remove the loading card
function removeLoadingCard() {
    const loadingCard = frame.querySelector('.loading-card');
    if (loadingCard) {
        frame.removeChild(loadingCard);
    }
}

// Utility function to toggle button state
function setButtonsDisabled(isDisabled) {
    const likeButton = document.querySelector(likeButtonSelector);
    const hateButton = document.querySelector(hateButtonSelector);

    if (likeButton) likeButton.disabled = isDisabled;
    if (hateButton) hateButton.disabled = isDisabled;

    if (isDisabled) {
        likeButton?.classList.add('disabled');
        hateButton?.classList.add('disabled');
    } else {
        likeButton?.classList.remove('disabled');
        hateButton?.classList.remove('disabled');
    }
}




// Initialize the swipe card system
initSwipeCards();
