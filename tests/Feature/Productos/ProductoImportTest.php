<?php

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

it('rechaza la importación cuando no existen categorías', function () {
    $archivo = UploadedFile::fake()->create('productos.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $response = postJson('/api/v1/productos/importar', [
        'archivo' => $archivo,
    ]);

    $response->assertStatus(422)
        ->assertJsonStructure(['success', 'message', 'errores'])
        ->assertJsonPath('success', false)
        ->assertJsonPath('errores', fn ($s) => str_contains((string) $s, 'No existen categorías registradas'));
});

it('no devuelve 422 de categorías cuando existe al menos una', function () {
    Categoria::factory()->create();

    $archivo = UploadedFile::fake()->create('productos.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $response = postJson('/api/v1/productos/importar', [
        'archivo' => $archivo,
    ]);

    // Nos aseguramos de que la validación de categorías no bloquea el flujo.
    if ($response->status() === 422) {
        $data = $response->json();
        expect($data['errores'] ?? '')->not->toContain('No existen categorías registradas');
    }

    // No importa si el import luego falla por otros motivos (e.g. archivo fake vacío);
    // basta con que la nueva regla no haya cortado la petición.
    expect(true)->toBeTrue();
});
