<?php

namespace WordPressBackEndChallenge;

defined('ABSPATH') || exit;

class RestHandler {
    private $dbHandler;
    private $restNamespace;

    public function __construct() {
        $this->dbHandler = new DbHandler();
        $this->restNamespace = self::getRestNamespace();

        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public static function getRestNamespace() {
        return 'wordpress-back-end-challenge/v1';
    }

    public function registerRoutes() {
        register_rest_route($this->restNamespace, '/favorite-posts', [
            'methods'  => 'GET',
            'callback' => [$this, 'getFavoritePosts'],
        ]);

        register_rest_route($this->restNamespace, '/favorite-posts', [
            'methods'  => 'POST',
            'callback' => [$this, 'addFavoritePost'],
        ]);
    }

    public function getFavoritePosts($request) {
        $user_id = $request->get_param('user_id');

        $favorites = $this->dbHandler->getAllFavoritesByUserId($user_id);

        if ($favorites) {
            return new \WP_REST_Response([
                'code' => 'favorite_posts_fetched',
                'status' => 'success',
                'message' => 'Favorite posts fetched',
                'data' => $favorites
            ], 200);
        }

        return new \WP_REST_Response([
            'code' => 'failed_to_fetch_favorite_posts',
            'status' => 'error',
            'message' => 'Failed to fetch favorite posts',
            'data' => null
        ], 500);
    }

    public function addFavoritePost($request) {
        $params = $request->get_json_params();

        $user_id = isset($params['user_id']) ? absint($params['user_id']) : absint($request->get_param('user_id'));
        $post_id = isset($params['post_id']) ? absint($params['post_id']) : absint($request->get_param('post_id'));

        if (!$user_id || !$post_id) {
            return new \WP_REST_Response([
                'code' => 'invalid_favorite_post_params',
                'status' => 'error',
                'message' => 'Invalid user_id or post_id',
                'data' => null
            ], 400);
        }

        $result = $this->dbHandler->addFavorite($user_id, $post_id);

        if ($result) {
            return new \WP_REST_Response([
                'code' => 'favorite_post_added',
                'status' => 'success',
                'message' => 'Favorite post added',
                'data' => [
                    'user_id' => $user_id,
                    'post_id' => $post_id
                ]
            ], 200);
        }

        return new \WP_REST_Response([
            'code' => 'failed_to_add_favorite_post',
            'status' => 'error',
            'message' => 'Failed to add favorite post',
            'data' => null
        ], 500);
    }

    public function removeFavoritePost($request) {
        $user_id = $request->get_param('user_id');
        $post_id = $request->get_param('post_id');

        $result = $this->dbHandler->removeFavorite($user_id, $post_id);

        if ($result) {
            return new \WP_REST_Response([
                'code' => 'favorite_post_removed',
                'status' => 'success',
                'message' => 'Favorite post removed',
                'data' => null
            ], 200);
        }

        return new \WP_REST_Response([
            'code' => 'failed_to_remove_favorite_post',
            'status' => 'error',
            'message' => 'Failed to remove favorite post',
            'data' => null
        ], 500);
    }
}
