<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById("screenshot-container")?.classList.add("!hidden");
    document.getElementById("docs-card")?.classList.add("!row-span-1");
    document.getElementById("docs-card-content")?.classList.add("!flex-row");
    document.getElementById("background")?.classList.add("!hidden");
}
</script>

<template>
    <Head title="Welcome" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div
            class="relative flex min-h-screen flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white"
        >
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header
                    class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3"
                >
                    <div class="flex lg:col-start-2 lg:justify-center">
                        <img
                            src="img/logo/kero.png"
                            alt=""
                            class="h-12 w-auto text-white lg:h-16 lg:text-[#FF2D20]"
                        />
                    </div>
                    <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition 
                            hover:text-black/70 focus:outline-none 
                            focus-visible:ring-[#FF2D20] 
                            dark:text-white 
                            dark:hover:text-white/80 
                            dark:focus-visible:ring-white"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 
                                text-black ring-1 ring-transparent transition 
                                hover:text-black/70 focus:outline-none 
                                focus-visible:ring-[#FF2D20] dark:text-white 
                                dark:hover:text-white/80 
                                dark:focus-visible:ring-white"
                            >
                                Log in
                            </Link>
                        </template>
                    </nav>
                </header>

                <main class="mt-6">
                    <div
                        class="mx-auto max-w-7xl py-24 sm:px-6 sm:py-32 lg:px-8"
                    >
                        <div
                            class="relative isolate overflow-hidden 
                            bg-gray-900 px-6 pt-16 shadow-2xl 
                            sm:rounded-3xl sm:px-16 md:pt-24 lg:flex 
                            lg:gap-x-20 lg:px-24 lg:pt-0"
                        >
                            <svg
                                viewBox="0 0 1024 1024"
                                class="absolute 
                                top-1/2 
                                left-1/2 -z-10 
                                size-[64rem] 
                                -translate-y-1/2 
                                [mask-image:radial-gradient(closest-side,white,transparent)] 
                                sm:left-full sm:-ml-80 lg:left-1/2 
                                lg:ml-0 
                                lg:-translate-x-1/2 
                                lg:translate-y-0"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="512"
                                    cy="512"
                                    r="512"
                                    fill="url(#759c1415-0410-454c-8f7c-9a820de03641)"
                                    fill-opacity="0.7"
                                />
                                <defs>
                                    <radialGradient
                                        id="759c1415-0410-454c-8f7c-9a820de03641"
                                    >
                                        <stop stop-color="#7775D6" />
                                        <stop offset="1" stop-color="#E935C1" />
                                    </radialGradient>
                                </defs>
                            </svg>
                            <div
                                class="mx-auto max-w-md text-center lg:mx-0 lg:flex-auto lg:py-32 lg:text-left"
                            >
                                <h2
                                    class="text-3xl font-semibold tracking-tight text-balance text-white sm:text-4xl"
                                >
                                    Welcome to Kero ERP
                                </h2>
                                <p
                                    class="mt-6 text-lg/8 text-pretty text-gray-300"
                                >
                                    Try login to access your module
                                </p>
                                <div
                                    class="mt-10 flex items-center justify-center gap-x-6 lg:justify-start"
                                >
                                    <Link
                                        :href="route('login')"
                                        class="rounded-md bg-white px-3.5 py-2.5 
                                        text-sm font-semibold text-gray-900 shadow-xs 
                                        hover:bg-gray-100 focus-visible:outline-2 
                                        focus-visible:outline-offset-2 focus-visible:outline-white"
                                        >Login Now</Link
                                    >
                                </div>
                            </div>
                            <div class="relative mt-16 h-80 lg:mt-8">
                                <img
                                    class="absolute top-0 left-0 w-[57rem] max-w-none rounded-md bg-white/5 ring-1 ring-white/10"
                                    src="img/Welcome/erp.jpg"
                                    alt="App screenshot"
                                    width="1824"
                                    height="1080"
                                />
                            </div>
                        </div>
                    </div>
                </main>

                <footer
                    class="py-16 text-center text-sm text-black dark:text-white/70"
                >
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </footer>
            </div>
        </div>
    </div>
</template>
