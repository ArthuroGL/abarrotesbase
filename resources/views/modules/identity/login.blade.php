<x-layouts.guest title="ABARROTESBASE - Iniciar Sesión">
    <div class="space-y-6">

        <!-- Branding del Sistema -->
        <div class="flex flex-col items-center justify-center text-center">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-500 font-black text-xl text-slate-950 shadow-md shadow-emerald-500/20 ring-4 ring-emerald-500/10">A</span>
            <div class="mt-3">
                <span class="block text-base font-black tracking-[0.18em] text-slate-900">ABARROTES</span>
                <span class="block text-xs font-bold tracking-[0.22em] text-emerald-600">BASE</span>
            </div>
        </div>

        <!-- Tarjeta del Formulario (Ancho controlado) -->
        <x-ui.card padding="p-12 sm:p-8" class="shadow-xl ring-slate-900/5">
            <div class="mb-6 text-center">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Acceso al sistema
                </h2>
                <p class="mt-1 text-xs text-slate-400">Ingresa tus credenciales para continuar</p>
            </div>

            <!-- Status de Sesión -->
            @if (session('status'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Correo Electrónico
                    </label>
                    <x-ui.input
                        id="email"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        placeholder="usuario@abarrotesbase.com"
                        :error="$errors->has('email')"
                    />
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Contraseña
                    </label>
                    <x-ui.input
                        id="password"
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        :error="$errors->has('password')"
                    />
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center space-x-2.5 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0"
                        >
                        <span class="text-xs font-medium text-slate-600">Recordar sesión</span>
                    </label>
                </div>

                <!-- Botón de Ingreso -->
                <div class="pt-2">
                    <x-ui.button type="submit" variant="primary" class="w-full justify-center shadow-md shadow-emerald-600/15">
                        Ingresar al Panel
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <!-- Footer -->
        <p class="text-center text-xs font-medium text-slate-400">
            Fundación técnica Laravel 12 · PostgreSQL
        </p>
    </div>
</x-layouts.guest>
