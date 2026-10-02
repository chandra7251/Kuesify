import re
with open('resources/js/Pages/LiveJoin.vue', 'r', encoding='utf-8') as f:
    content = f.read()

content = re.sub(r'initCanvas\(\);\s*', '', content)

script_part = content.split('<template>')[0]

new_template = '''<template>
    <Head title="Masuk ke Sesi Live" />
    <main
        ref="root"
        class="relative min-h-screen overflow-hidden bg-brand-accent text-brand-primary selection:bg-brand-secondary selection:text-white"
    >
        <!-- Ambient Background Blobs -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -left-[10%] top-[-10%] h-[45vw] w-[45vw] rounded-full bg-brand-secondary/20 mix-blend-multiply blur-[80px]"
            ></div>
            <div
                class="absolute -right-[10%] bottom-[-10%] h-[40vw] w-[40vw] rounded-full bg-brand-primary/10 mix-blend-multiply blur-[80px]"
            ></div>
        </div>

        <!-- Navigation -->
        <nav class="sticky top-0 z-40 w-full bg-white/80 backdrop-blur border-b border-brand-primary/10">
            <div class="mx-auto flex h-16 sm:h-20 max-w-7xl items-center justify-between px-5 sm:px-8 lg:px-12">
                <Link href="/" class="flex items-center gap-2.5 transition-transform hover:scale-105">
                    <ApplicationLogo class="h-8 w-8 text-brand-secondary" />
                    <span class="text-xl font-black tracking-[-0.04em] text-brand-primary">
                        kuesify
                    </span>
                </Link>
                <div class="flex items-center gap-3">
                    <Link
                        href="/login"
                        class="hidden sm:flex items-center justify-center rounded-2xl bg-brand-primary/10 px-5 py-2.5 text-sm font-black text-brand-primary transition hover:bg-brand-primary/20"
                    >
                        Masuk →
                    </Link>
                </div>
            </div>
        </nav>

        <div class="relative z-10 mx-auto flex min-h-[calc(100vh-5rem)] max-w-6xl flex-col items-center justify-center p-6 lg:flex-row lg:gap-16 lg:p-8">
            
            <!-- Left Panel (Branding / Mascot) -->
            <div class="flex w-full flex-col items-center text-center lg:w-1/2 lg:items-start lg:text-left mb-10 lg:mb-0">
                <div class="inline-flex items-center gap-2 rounded-full bg-brand-secondary/15 px-4 py-2 mb-6">
                    <span class="relative flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-secondary opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-brand-secondary"></span>
                    </span>
                    <span class="text-xs font-black uppercase tracking-wider text-brand-secondary">
                        Arena Live Aktif
                    </span>
                </div>
                
                <h1 class="text-4xl font-black leading-[1.1] tracking-[-0.05em] text-brand-primary sm:text-5xl lg:text-6xl">
                    Masuk ke <br class="hidden lg:block" />Sesi Live!
                </h1>
                
                <p class="mt-4 max-w-sm text-base text-brand-primary/70 sm:text-lg">
                    Minta PIN dari gurumu, masukkan namamu, dan jadilah yang tercepat di panggung.
                </p>

                <!-- Mascot -->
                <div class="relative mt-8 max-w-[200px] lg:max-w-[280px]">
                    <img 
                        src="/images/study-character-male.png" 
                        alt="Kuesify Mascot" 
                        ref="mascotEl"
                        class="relative z-10 drop-shadow-2xl"
                    />
                    
                    <!-- Speech Bubble -->
                    <div 
                        ref="speechBubbleEl"
                        class="absolute -right-8 -top-8 z-20 w-44 rounded-2xl bg-white p-3 shadow-figma sm:-right-16 sm:-top-4 lg:-right-24 lg:-top-6"
                    >
                        <div class="absolute -bottom-2 left-6 h-4 w-4 rotate-45 bg-white"></div>
                        <p class="relative z-10 text-xs font-bold leading-tight text-brand-primary">
                            {{ form.pin.length === 6 ? 'PIN lengkap! Masukkan namamu dan ayo mulai! 🚀' : mascotSpeech }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Panel (Form) -->
            <div class="w-full max-w-md lg:w-1/2">
                <section data-anim-card class="relative rounded-3xl bg-white p-6 shadow-figma sm:p-8">
                    <!-- Form -->
                    <form @submit.prevent="join" class="flex flex-col gap-6">
                        
                        <!-- PIN Section -->
                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <label for="live-pin-input" class="text-sm font-black text-brand-primary">
                                    PIN Game <span class="text-red-500">*</span>
                                </label>
                                <span class="text-xs font-bold text-brand-primary/60">
                                    {{ form.pin.length }} / 6
                                </span>
                            </div>
                            
                            <input
                                id="live-pin-input"
                                type="text"
                                inputmode="numeric"
                                autocomplete="off"
                                :value="form.pin"
                                @input="sanitizePin"
                                class="absolute h-0 w-0 opacity-0"
                                ref="hiddenInput"
                            />
                            
                            <!-- Custom PIN Dots -->
                            <div 
                                class="flex cursor-text justify-between gap-2 sm:gap-3"
                                @click="$refs.hiddenInput?.focus()"
                                ref="pinContainerEl"
                            >
                                <div
                                    v-for="i in 6"
                                    :key="i"
                                    class="flex h-12 w-full items-center justify-center rounded-xl border-2 font-black transition-all sm:h-14 sm:text-xl"
                                    :class="[
                                        form.pin.length >= i
                                            ? 'border-brand-secondary bg-brand-secondary text-white'
                                            : form.pin.length === i - 1
                                            ? 'scale-105 border-brand-secondary bg-brand-secondary/10 text-brand-primary ring-4 ring-brand-secondary/20'
                                            : 'border-brand-primary/20 bg-brand-accent text-brand-primary/30'
                                    ]"
                                >
                                    {{ form.pin[i - 1] || '' }}
                                </div>
                            </div>
                            
                            <span v-if="form.errors.pin" class="mt-2 block text-xs font-bold text-red-500">
                                {{ form.errors.pin }}
                            </span>
                        </div>

                        <!-- Numpad -->
                        <div class="rounded-2xl bg-brand-accent/50 p-4">
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <button
                                    v-for="num in [1, 2, 3, 4, 5, 6, 7, 8, 9]"
                                    :key="num"
                                    type="button"
                                    class="flex h-12 items-center justify-center rounded-xl border border-brand-primary/10 bg-brand-accent text-lg font-black text-brand-primary transition hover:bg-brand-primary hover:text-white active:scale-95 sm:h-13"
                                    @click="pressKey(num.toString())"
                                >
                                    {{ num }}
                                </button>
                                <button
                                    type="button"
                                    class="flex h-12 items-center justify-center rounded-xl border border-red-100 bg-red-50 text-sm font-black text-red-500 transition hover:bg-red-500 hover:text-white active:scale-95 sm:h-13"
                                    @click="pressKey('clear')"
                                >
                                    C
                                </button>
                                <button
                                    type="button"
                                    class="flex h-12 items-center justify-center rounded-xl border border-brand-primary/10 bg-brand-accent text-lg font-black text-brand-primary transition hover:bg-brand-primary hover:text-white active:scale-95 sm:h-13"
                                    @click="pressKey('0')"
                                >
                                    0
                                </button>
                                <button
                                    type="button"
                                    class="flex h-12 items-center justify-center rounded-xl border border-amber-100 bg-amber-50 text-sm font-black text-amber-600 transition hover:bg-amber-500 hover:text-white active:scale-95 sm:h-13"
                                    @click="pressKey('back')"
                                >
                                    ⌫
                                </button>
                            </div>
                        </div>

                        <!-- Nickname Input Section -->
                        <div>
                            <label for="live-alias-input" class="mb-2 block text-sm font-black text-brand-primary">
                                Nama Panggilan Kamu <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="live-alias-input"
                                v-model="form.alias"
                                maxlength="25"
                                placeholder="Contoh: Sang Juara 🏆"
                                autocomplete="nickname"
                                class="block min-h-12 w-full rounded-xl border-2 border-brand-primary/20 bg-brand-accent/30 px-4 text-sm font-bold text-brand-primary placeholder:text-brand-primary/40 transition focus:border-brand-secondary focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-secondary/20"
                            />
                            <span v-if="form.errors.alias" class="mt-1 block text-xs font-bold text-red-500">
                                {{ form.errors.alias }}
                            </span>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                class="group relative flex min-h-14 w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-brand-primary px-6 text-base font-black text-white shadow-figma transition-all hover:-translate-y-1 hover:shadow-figma-hover active:translate-y-1 active:shadow-none disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="form.processing || form.pin.length !== 6 || !form.alias.trim()"
                            >
                                <span v-if="form.processing" class="flex items-center gap-2">
                                    <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                    </svg>
                                    Menghubungkan...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    Masuk dan Mulai 🚀
                                </span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
            
        </div>
    </main>
</template>'''

with open('resources/js/Pages/LiveJoin.vue', 'w', encoding='utf-8') as f:
    f.write(script_part + new_template)
