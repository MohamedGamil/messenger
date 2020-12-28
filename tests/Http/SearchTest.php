<?php

namespace RTippin\Messenger\Tests\Http;

use RTippin\Messenger\Facades\Messenger;
use RTippin\Messenger\Tests\FeatureTestCase;
use RTippin\Messenger\Tests\UserModel;

class SearchTest extends FeatureTestCase
{
    /** @test */
    public function guest_is_unauthorized()
    {
        $this->getJson(route('api.messenger.search', [
            'query' => 'john',
        ]))
            ->assertUnauthorized();
    }

    /** @test */
    public function empty_search_returns_no_results()
    {
        $this->actingAs(UserModel::find(1));

        $this->getJson(route('api.messenger.search'))
            ->assertJsonCount(0, 'data')
            ->assertJson([
                'meta' => [
                    'total' => 0,
                    'search_items' => [],
                    'per_page' => Messenger::getSearchPageCount(),
                    'search' => '',
                ],
            ]);
    }

    /** @test */
    public function search_finds_user()
    {
        $this->actingAs(UserModel::find(1));

        $this->getJson(route('api.messenger.search', [
            'query' => 'tippin',
        ]))
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Richard Tippin',
                    ],
                ],
                'meta' => [
                    'total' => 1,
                    'search_items' => [
                        'tippin',
                    ],
                    'per_page' => Messenger::getSearchPageCount(),
                    'search' => 'tippin',
                ],
            ]);
    }

    /** @test */
    public function search_for_user_without_messenger_returns_no_results()
    {
        $this->generateJaneSmith();

        $this->actingAs(UserModel::find(1));

        $this->getJson(route('api.messenger.search', [
            'query' => 'jane',
        ]))
            ->assertJsonCount(0, 'data')
            ->assertJson([
                'meta' => [
                    'total' => 0,
                    'search_items' => [
                        'jane',
                    ],
                    'per_page' => Messenger::getSearchPageCount(),
                    'search' => 'jane',
                ],
            ]);
    }

    /** @test */
    public function multiple_search_queries_separated_by_space_returns_multiple_results()
    {
        $this->actingAs(UserModel::find(1));

        $this->getJson(route('api.messenger.search', [
            'query' => 'tippin john',
        ]))
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'meta' => [
                    'total' => 2,
                    'search_items' => [
                        'tippin',
                        'john',
                    ],
                    'per_page' => Messenger::getSearchPageCount(),
                    'search' => 'tippin john',
                ],
            ]);
    }

    /** @test */
    public function search_strips_special_characters()
    {
        $this->actingAs(UserModel::find(1));

        $this->getJson(route('api.messenger.search', [
            'query' => '%`tippin"><',
        ]))
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'meta' => [
                    'total' => 1,
                    'search_items' => [
                        'tippin',
                    ],
                    'per_page' => Messenger::getSearchPageCount(),
                    'search' => 'tippin',
                ],
            ]);
    }

    /** @test */
    public function exact_email_returns_user_result()
    {
        $this->actingAs(UserModel::find(1));

        $this->getJson(route('api.messenger.search', [
            'query' => 'richard.tippin@gmail.com',
        ]))
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'name' => 'Richard Tippin',
                    ],
                ],
                'meta' => [
                    'total' => 1,
                    'search_items' => [
                        'richard.tippin@gmail.com',
                    ],
                    'per_page' => Messenger::getSearchPageCount(),
                    'search' => 'richard.tippin@gmail.com',
                ],
            ]);
    }

    /** @test */
    public function incomplete_email_returns_no_results()
    {
        $this->actingAs(UserModel::find(1));

        $this->getJson(route('api.messenger.search', [
            'query' => 'richard.tippin',
        ]))
            ->assertJsonCount(0, 'data')
            ->assertJson([
                'meta' => [
                    'total' => 0,
                    'search_items' => [
                        'richard.tippin',
                    ],
                    'per_page' => Messenger::getSearchPageCount(),
                    'search' => 'richard.tippin',
                ],
            ]);
    }
}