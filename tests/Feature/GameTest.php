<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    private string $userToken = '';
    private int $existingGameId;

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

        $user = User::factory()->createOne();
        $user->games()->save($game);
        $user->refresh();
        $game->refresh();

        $this->userToken = $user->createToken('test')->plainTextToken;
        $this->existingGameId = $game->id;
    }

    public function testBrowseSucceeds() : void
    {
        // todo update this test to assert that a paginated response was given
        //  in order for this test to pass, you will need to seed at least 1 game
        $this
            ->get('games')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'name',
                'user_id',
                'created_at',
                'updated_at'
            ]);
    }

    public function testCreateSucceedsWhileAuthenticated() : void
    {
        $this
            ->post('games', [
                'name' => 'Rogue Knight'
            ], $this->getHeaders())
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'id',
                'name',
                'user_id',
                'created_at',
                'updated_at'
            ])
            ->assertJsonFragment([
                'name' => 'Rogue Knight'
            ]);
    }

    public function testReadSucceeds() : void
    {
        // todo create the game that we are going to view, adding the required authentication
        $response = $this
            ->post('games', [
            'name' => 'Rogue Knight'
        ], $this->getHeaders());

        $this
            ->get('games/'.$response->json('id'), $this->getHeaders())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'id',
                'name',
                'user_id',
                'created_at',
                'updated_at'
            ])
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
            ->assertJsonStructure([
                'id',
                'name',
                'user_id',
                'created_at',
                'updated_at'
            ])
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
            ->assertJsonStructure([
                'id',
                'name',
                'user_id',
                'created_at',
                'updated_at'
            ])
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
