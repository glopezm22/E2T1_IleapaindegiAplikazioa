<?php

use App\Models\Portfolio;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function crearUsuarioConToken(): array
{
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    return [$user, $token];
}

function crearEstudiante(): Student
{
    return Student::create([
        'name'     => 'Ane',
        'surnames' => 'Etxebarria',
    ]);
}

// ─── Autenticación ──────────────────────────────────────────────

test('portfolios requiere autenticación', function () {
    $this->getJson('/api/portfolios')->assertStatus(401);
});

// ─── Listado ────────────────────────────────────────────────────

test('usuario autenticado puede listar portfolios', function () {
    [, $token] = crearUsuarioConToken();

    $this->withToken($token)
        ->getJson('/api/portfolios')
        ->assertStatus(200)
        ->assertJsonIsArray();
});

test('listado filtra por student_id', function () {
    Storage::fake('public');
    [, $token] = crearUsuarioConToken();

    $estudiante1 = crearEstudiante();
    $estudiante2 = Student::create(['name' => 'Miren', 'surnames' => 'Lopez']);

    Portfolio::create([
        'student_id'   => $estudiante1->id,
        'category'     => 'cortes',
        'photo_before' => 'portfolio/a.jpg',
        'photo_after'  => 'portfolio/b.jpg',
    ]);

    Portfolio::create([
        'student_id'   => $estudiante2->id,
        'category'     => 'colores',
        'photo_before' => 'portfolio/c.jpg',
        'photo_after'  => 'portfolio/d.jpg',
    ]);

    $res = $this->withToken($token)
        ->getJson("/api/portfolios?student_id={$estudiante1->id}")
        ->assertStatus(200);

    expect($res->json())->toHaveCount(1);
    expect($res->json()[0]['student_id'])->toBe($estudiante1->id);
});

// ─── Subida ─────────────────────────────────────────────────────

test('puede subir fotos al portfolio', function () {
    Storage::fake('public');
    [, $token] = crearUsuarioConToken();
    $estudiante = crearEstudiante();

    $res = $this->withToken($token)
        ->post('/api/portfolios', [
            'student_id'   => $estudiante->id,
            'category'     => 'mechas',
            'photo_before' => UploadedFile::fake()->image('antes.jpg'),
            'photo_after'  => UploadedFile::fake()->image('despues.jpg'),
            'notes'        => 'Trabajo de fin de curso',
        ], ['Accept' => 'application/json']);

    $res->assertStatus(201)
        ->assertJsonFragment(['category' => 'mechas'])
        ->assertJsonPath('student.id', $estudiante->id);

    expect(Portfolio::count())->toBe(1);
});

test('subida falla sin photo_before', function () {
    Storage::fake('public');
    [, $token] = crearUsuarioConToken();
    $estudiante = crearEstudiante();

    $this->withToken($token)
        ->post('/api/portfolios', [
            'student_id'  => $estudiante->id,
            'category'    => 'cortes',
            'photo_after' => UploadedFile::fake()->image('despues.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['photo_before']);
});

test('subida falla con categoría inválida', function () {
    Storage::fake('public');
    [, $token] = crearUsuarioConToken();
    $estudiante = crearEstudiante();

    $this->withToken($token)
        ->post('/api/portfolios', [
            'student_id'   => $estudiante->id,
            'category'     => 'invalida',
            'photo_before' => UploadedFile::fake()->image('antes.jpg'),
            'photo_after'  => UploadedFile::fake()->image('despues.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['category']);
});

// ─── Ver detalle ────────────────────────────────────────────────

test('puede ver una entrada del portfolio', function () {
    Storage::fake('public');
    [, $token] = crearUsuarioConToken();
    $estudiante = crearEstudiante();

    $entrada = Portfolio::create([
        'student_id'   => $estudiante->id,
        'category'     => 'tratamientos',
        'photo_before' => 'portfolio/x.jpg',
        'photo_after'  => 'portfolio/y.jpg',
    ]);

    $this->withToken($token)
        ->getJson("/api/portfolios/{$entrada->id}")
        ->assertStatus(200)
        ->assertJsonPath('id', $entrada->id)
        ->assertJsonPath('category', 'tratamientos');
});

// ─── Eliminación ────────────────────────────────────────────────

test('puede eliminar una entrada del portfolio', function () {
    Storage::fake('public');
    [, $token] = crearUsuarioConToken();
    $estudiante = crearEstudiante();

    $entrada = Portfolio::create([
        'student_id'   => $estudiante->id,
        'category'     => 'colores',
        'photo_before' => 'portfolio/p.jpg',
        'photo_after'  => 'portfolio/q.jpg',
    ]);

    $this->withToken($token)
        ->deleteJson("/api/portfolios/{$entrada->id}")
        ->assertStatus(204);

    expect(Portfolio::find($entrada->id))->toBeNull();
});
