<x-layouts.guest title="ABARROTESBASE - Iniciar Sesión">

    <div class="mx-auto w-full max-w-md space-y-6">

        {{-- Branding --}}
        <div class="flex flex-col items-center text-center">

            <span
                class="grid h-14 w-14 place-items-center rounded-2xl
                       bg-emerald-500 font-black text-xl text-slate-950
                       shadow-lg shadow-emerald-500/20
                       ring-4 ring-emerald-500/10"
                aria-hidden="true"
            >
                A
            </span>

            <div class="mt-3">
                <span
                    class="block text-base font-black tracking-[0.18em] text-slate-900"
                >
                    ABARROTES
                </span>

                <span
                    class="block text-xs font-bold tracking-[0.22em] text-emerald-600"
                >
                    MARGARITA
                </span>
            </div>

        </div>


        {{-- Login --}}
        <x-ui.card
            padding="p-6 sm:p-8"
            class="shadow-xl shadow-slate-900/5"
        >

            <div class="mb-7 text-center">

                <h1 class="text-base font-black text-slate-900">
                    Acceso al sistema
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Ingresa tus credenciales para continuar
                </p>

            </div>


            {{-- Status --}}
            @if (session('status'))

                <div
                    class="mb-5 rounded-xl border border-emerald-200
                           bg-emerald-50 px-4 py-3 text-sm font-semibold
                           text-emerald-800"
                    role="status"
                >
                    {{ session('status') }}
                </div>

            @endif


            <form
                method="POST"
                action="{{ route('login') }}"
                class="space-y-5"
                novalidate
            >

                @csrf


                {{-- Email --}}
                <div>

                    <label
                        for="email"
                        class="mb-2 block text-xs font-black
                               uppercase tracking-[0.08em]
                               text-slate-600"
                    >
                        Correo electrónico
                    </label>

                    <x-ui.input
                        id="email"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                        inputmode="email"
                        placeholder="usuario@abarrotesbase.com"
                        :error="$errors->has('email')"
                        aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                    />

                    @error('email')
                        <p
                            id="email-error"
                            class="mt-2 text-sm font-semibold text-rose-600"
                            role="alert"
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Password --}}
                <div>

                    <label
                        for="password"
                        class="mb-2 block text-xs font-black
                               uppercase tracking-[0.08em]
                               text-slate-600"
                    >
                        Contraseña
                    </label>

                    <x-ui.input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        :error="$errors->has('password')"
                        aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                    />

                    @error('password')
                        <p
                            id="password-error"
                            class="mt-2 text-sm font-semibold text-rose-600"
                            role="alert"
                        >
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Remember --}}
                <div class="flex items-center pt-1">

                    <label
                        for="remember"
                        class="flex cursor-pointer select-none
                               items-center gap-2.5"
                    >

                        <input
                            id="remember"
                            type="checkbox"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                            class="h-4 w-4 rounded border-slate-300
                                   text-emerald-600
                                   focus:ring-2 focus:ring-emerald-500
                                   focus:ring-offset-0"
                        >

                        <span class="text-sm font-medium text-slate-600">
                            Recordar sesión
                        </span>

                    </label>

                </div>


                {{-- Submit --}}
                <div class="pt-1">

                    <x-ui.button
                        type="submit"
                        variant="primary"
                        class="w-full justify-center
                               shadow-md shadow-emerald-600/15"
                    >
                        Ingresar al panel
                    </x-ui.button>

                </div>

            </form>

        </x-ui.card>


        {{-- Footer --}}
        <p class="text-center text-xs font-medium text-slate-400">
            Laravel 12 · PostgreSQL
        </p>

    </div>

</x-layouts.guest>
