<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Game;
use App\Models\Mod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class ModTest extends TestCase
{
    use RefreshDatabase;

    private string $userToken;
    private int $userId;
    private int $existingGameId;
    private int $existingModId;

    const MOD_STRUCTURE = [
        'id',
        'name',
        'game_id',
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

        $user = User::factory()->createOne();
        $user->refresh();

        $game = Game::factory()
            ->for($user)
            ->createOne();
        $game->refresh();
        $this->existingGameId = $game->id;

        $mod = Mod::factory()
            ->for($user)
            ->for($game)
            ->createOne();
        $mod->refresh();

        $this->existingModId = $mod->id;

        mod::factory()
            ->count(30)
            ->for($user)
            ->for($game)
            ->create();

        $user->refresh();
        $game->refresh();

        $this->userToken = $user->createToken('test')->plainTextToken;
        $this->userId = $user->id;
    }

    public function testBrowseSucceeds() : void
    {
        // todo update this test to assert that a paginated response was given.
        //  in order for this test to pass, you will need to seed at least 1 game
        //  and 1 mod
        $game = Game::inRandomOrder()->first();
        $mod = Game::query()->where('game', '=', $game->id)->first();

        $this
            ->get('games/'.$game->id.'/mods')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(self::MOD_STRUCTURE)
            ->assertJsonFragment([
                'id' => $mod->id
                // todo assert game is valid
                // todo assert user is valid
            ]);
    }

    public function testCreateSucceedsWhileAuthenticated() : void
    {
        $game = Game::inRandomOrder()->first();

        $this
            ->post('games/'.$game->id.'/mods', [
                'name' => 'Lightsaber'
            ], $this->getHeaders())
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(self::MOD_STRUCTURE)
            ->assertJsonFragment([
                'name' => 'Lightsaber',
                'game_id' => $game->id,
                'user_id' => $this->userId,
            ]);
    }

    public function testReadSucceeds() : void
    {
        // Create a game
        $gameResponse = $this->post('games', [
            'name' => 'Rogue Knight'
        ], $this->getHeaders())->assertStatus(Response::HTTP_CREATED);

        // Create a mod against the game
        $modResponse = $this->post('games/'.$gameResponse->json('id').'/mods', [
            'name' => 'Lightsaber'
        ], $this->getHeaders())->assertStatus(Response::HTTP_CREATED);

        // view the mod
        $this
            ->get('games/'.$gameResponse->json('id').'/mods/'.$modResponse->json('id'),
            $this->getHeaders())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(self::MOD_STRUCTURE)
            ->assertJsonFragment([
                'name' => 'Lightsaber',
                'game_id' => $gameResponse->json('id'),
                'user_id' => $this->userId,
            ]);
    }

    public function testCreateFailsWhileUnauthenticated() : void
    {
        $game = Game::inRandomOrder()->first();

        $this
            ->post('games/'.$game->id.'/mods', [
                'name' => 'Lightsaber'
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdateSucceedsWhileAuthenticated() : void
    {
        // Create the game
        $gameResponse = $this->post('games', [
            'name' => 'Rogue Knight'
        ], $this->getHeaders())->assertStatus(Response::HTTP_CREATED);

        // Create the mod
        $modResponse = $this->post('games/'.$gameResponse->json('id').'/mods', [
            'name' => 'Lightsaber'
        ], $this->getHeaders())->assertStatus(Response::HTTP_CREATED);

        // Update the mod
        $this
            ->put('games/'.$gameResponse->json('id').'/mods/'.$modResponse->json('id'), [
                'name' => 'Lightsabers (Full set)'
            ], $this->getHeaders())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(self::MOD_STRUCTURE)
            ->assertJsonFragment([
                'name' => 'Lightsabers (Full set)',
                'game_id' => $gameResponse->json('id'),
                'user_id' => $this->userId,
            ]);
    }

    public function testUpdateFailsWhileUnauthenticated() : void
    {
        // Rather than creating the game here, due to a current issue with Sanctum,
        // we need to create a game to edit during setup.

        // this however should fail with 401 Unauthorized, as expected
        $this
            ->put('games/'.$this->existingGameId.'/mods/'.$this->existingModId, [
                'name' => 'Lightsabers (Full set)'
            ])
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testDeleteSucceedsWhileAuthenticated() : void
    {
        // Create the game
        $gameResponse = $this->post('games', [
            'name' => 'Rogue Knight'
        ], $this->getHeaders());

        // Create the mod
        $modResponse = $this->post('games/'.$gameResponse->json('id').'/mods', [
            'name' => 'Lightsaber'
        ], $this->getHeaders())->assertStatus(Response::HTTP_CREATED);

        // and just for sanity we make sure it actually got created
        $this
            ->get('games/'.$gameResponse->json('id').'/mods/'.$modResponse->json('id'),
            $this->getHeaders())
            ->assertStatus(Response::HTTP_OK);

        // then we finally attempt to delete it without authentication present
        $this
            ->delete('games/'.$gameResponse->json('id').'/mods/'.$modResponse->json('id'),
            $this->getHeaders())
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteFailsWhileUnauthenticated() : void
    {
        // Rather than creating the game here, due to a current issue with Sanctum,
        // we need to create a game to edit during setup.

        // Attempt to delete it without authentication present
        $this
            ->delete('games/'.$this->existingGameId.'/mods/'.$this->existingModId)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
