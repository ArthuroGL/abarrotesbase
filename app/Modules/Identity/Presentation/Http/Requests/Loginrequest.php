<?php

declare(strict_types=1);

namespace App\Modules\Identity\Presentation\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normaliza los datos antes de ejecutar la validación.
     */
    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (is_string($email)) {
            $this->merge([
                'email' => strtolower(trim($email)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' =>
                'El correo electrónico es obligatorio.',

            'email.string' =>
                'El correo electrónico debe ser válido.',

            'email.email' =>
                'Ingresa un correo electrónico válido.',

            'email.max' =>
                'El correo electrónico no puede superar los 255 caracteres.',

            'password.required' =>
                'La contraseña es obligatoria.',

            'remember.boolean' =>
                'El valor de recordar sesión no es válido.',
        ];
    }

    /**
     * Intenta autenticar al usuario.
     */
    public function authenticate(): void
    {
        $email = $this->string('email')->toString();
        $password = $this->string('password')->toString();

        /*
         * Buscamos el usuario ignorando mayúsculas/minúsculas
         * en el correo electrónico.
         */
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        /*
         * Verificamos las credenciales manualmente para que
         * la búsqueda del correo sea case-insensitive.
         */
        if (
            ! $user ||
            ! password_verify($password, $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' =>
                    'El correo electrónico o la contraseña no son correctos.',
            ]);
        }

        /*
         * Verificar que la cuenta esté activa.
         */
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' =>
                    'Tu cuenta se encuentra inactiva.',
            ]);
        }

        /*
         * Crear la sesión autenticada.
         */
        Auth::login(
            $user,
            $this->boolean('remember')
        );

        /*
         * Actualizar último inicio de sesión.
         */
        $user->forceFill([
            'last_login_at' => now(),
        ])->save();
    }
}
