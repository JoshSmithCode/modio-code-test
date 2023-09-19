<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    private string $userToken = '';
    private int $existingGameId;

    const GAME_STRUCTURE = [
        'id',
        'name',
        'user_id',
        'created_at',
        'updated_at'
    ];

    /**
     * @return string[]
     */
    private function getHeaders()
    {
        return [
            'Authorization' => 'Bearer '. $this->userToken,
            'Accept' => 'application/json',
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $game = new Game(['name' => 'Test game']);

        Game::factory()
            ->count(30)
            ->for(User::factory()->createOne())
            ->create();

        $user = User::factory()->createOne();
        $user->games()->save($game);

        $user->refresh();
        $game->refresh();

        $this->userToken = $user->createToken('test')->plainTextToken;
        $this->existingGameId = $game->id;
    }

    public function testBrowseSucceeds() : void
    {
        $this
            ->get('games')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'total',
            'per_page',
            'current_page',
            'last_page',
            'first_page_url',
            'last_page_url',
            'next_page_url',
            'from',
            'to',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'name',
                    'created_at',
                    'updated_at'
                ]
            ],
        ]);
    }

    public function testCreateSucceedsWhileAuthenticated() : void
    {
        $this
            ->post('games', [
                'name' => 'Rogue Knight'
            ], $this->getHeaders())
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(self::GAME_STRUCTURE)
            ->assertJsonFragment([
                'name' => 'Rogue Knight'
            ]);
    }

    public function testReadSucceeds() : void
    {
        $response = $this
            ->post('games', [
            'name' => 'Rogue Knight'
        ], $this->getHeaders());

        $this
            ->get('games/'.$response->json('id'), $this->getHeaders())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(self::GAME_STRUCTURE)
            ->assertJsonFragment([
                'name' => 'Rogue Knight',
            ]);
    }

    public function testCreateFailsWhileUnauthenticated() : void
    {
        $this
            ->post('games', [
                'name' => 'Rogue Knight'
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdateSucceedsWhileAuthenticated() : void
    {
        $response = $this->post('games', [
            'name' => 'Rogue Knight'
        ], $this->getHeaders());

        $this
            ->put('games/'.$response->json('id'), [
                'name' => 'Rogue Knight Remastered'
            ], $this->getHeaders())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(self::GAME_STRUCTURE)
            ->assertJsonFragment([
                'name' => 'Rogue Knight Remastered'
            ]);
    }

    public function testUpdateFailsWhileUnauthenticated() : void
    {
        // Rather than creating the game here, due to a current issue with Sanctum,
        // we need to create a game to edit during setup.

        // this however should fail with 401 Unauthorized, as expected
        $this
            ->put('games/'.$this->existingGameId, [
                'name' => 'Rogue Knight Remastered'
            ], ['HTTP_FAKE' => 'true'])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

    }

    public function testDeleteSucceedsWhileAuthenticated() : void
    {
        // todo again create the game, include the auth.
        $response = $this->withHeaders($this->getHeaders())->post('games', [
            'name' => 'Rogue Knight'
        ]);

        // just to ensure the game actually exists
        $this
            ->withHeaders($this->getHeaders())
            ->get('games/'.$response->json('id'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(self::GAME_STRUCTURE)
            ->assertJsonFragment([
                'name' => 'Rogue Knight'
            ]);

        // todo include the auth
        $this
            ->withHeaders($this->getHeaders())
            ->delete('games/'.$response->json('id'))
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteFailsWhileUnauthenticated() : void
    {
        // Rather than creating the game here, due to a current issue with Sanctum,
        // we need to create a game to delete during setup.

        // then we finally attempt to delete it without authentication present
        $this
            ->withoutToken()
            ->delete('games/'.$this->existingGameId)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
