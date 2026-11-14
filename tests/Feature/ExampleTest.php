<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_un_usuario_autenticado_accede_a_ordenes(): void
    {
        $usuario = User::factory()->create(['rol' => 'recepcion']);

        $this->actingAs($usuario)->get('/')->assertRedirect(route('ordenes.index'));
        $this->actingAs($usuario)->get(route('ordenes.index'))->assertOk();
    }
}
