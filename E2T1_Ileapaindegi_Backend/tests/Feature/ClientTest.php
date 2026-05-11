<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function autenticar(): string
{
    $user = User::factory()->create();
    return $user->createToken('test')->plainTextToken;
}

// ─── Listado ────────────────────────────────────────────────────

test('puede listar clientes autenticado', function () {
    $token = autenticar();
    Client::create(['name' => 'Ane', 'surnames' => 'Etxebarria', 'home_client' => false]);

    $this->withToken($token)
        ->getJson('/api/clients')
        ->assertStatus(200)
        ->assertJsonIsArray()
        ->assertJsonFragment(['name' => 'Ane']);
});

test('listar clientes sin auth devuelve 401', function () {
    $this->getJson('/api/clients')->assertStatus(401);
});

// ─── Creación ────────────────────────────────────────────────────

test('puede crear un cliente con datos válidos', function () {
    $token = autenticar();

    $this->withToken($token)
        ->postJson('/api/clients', [
            'name'        => 'Miren',
            'surnames'    => 'Larrañaga',
            'telephone'   => '600111222',
            'email'       => 'miren@test.com',
            'home_client' => false,
        ])
        ->assertStatus(201)
        ->assertJsonFragment(['name' => 'Miren']);

    expect(Client::where('name', 'Miren')->exists())->toBeTrue();
});

test('crear cliente falla sin nombre', function () {
    $token = autenticar();

    $this->withToken($token)
        ->postJson('/api/clients', [
            'surnames'    => 'Larrañaga',
            'home_client' => false,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

// ─── Actualización ───────────────────────────────────────────────

test('puede actualizar un cliente', function () {
    $token = autenticar();
    $cliente = Client::create(['name' => 'Ane', 'surnames' => 'Lopez', 'home_client' => false]);

    $this->withToken($token)
        ->putJson("/api/clients/{$cliente->id}", [
            'name'        => 'Ane',
            'surnames'    => 'Lopez',
            'email'       => 'ane@nuevoemail.com',
            'home_client' => false,
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['email' => 'ane@nuevoemail.com']);
});

// ─── Eliminación ────────────────────────────────────────────────

test('puede eliminar un cliente', function () {
    $token = autenticar();
    $cliente = Client::create(['name' => 'Borrar', 'surnames' => 'Este', 'home_client' => false]);

    $this->withToken($token)
        ->deleteJson("/api/clients/{$cliente->id}")
        ->assertStatus(204);

    expect(Client::find($cliente->id))->toBeNull();
});
