const favoritePosts = {
    init() {
        if (!this.component) return;

        this.addEventListeners();
    },

    addEventListeners() {
        this.component.addEventListener('click', this.handleClick.bind(this));
    },

    async getUserId() {
        // @ts-ignore
        return fetch(wpApiSettings.root + 'wp/v2/users/me', {
            method: 'GET',
            headers: {
                // @ts-ignore
                'X-WP-Nonce': wpApiSettings.nonce
            }
        })
        .then(response => response.json())
        .then(user => user.id)
        .catch(error => {
            console.error('Erro:', error);
            return null;
        });
    },

    async handleClick() {
        const userId = await this.getUserId();
        if (!userId) return;

        // @ts-ignore
        fetch(wpApiSettings.root + wpApiSettings.rest_namespace + '/favorite-posts', {
            method: 'POST',
            body: JSON.stringify({
                post_id: this.component.dataset.postId,
                user_id: userId
            }),
            headers: {
                'Content-Type': 'application/json',
                // @ts-ignore
                'X-WP-Nonce': wpApiSettings.nonce
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Erro:', error);
        });
    },

    component: document.querySelector('.favorite-post-button'),
}

favoritePosts.init();
