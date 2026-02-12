<?php

$settings_page = admin_url( 'admin.php?page=kc-uf-settings' );

?>


<div class="wrap">
    <div id="" class="">
        <div class="relative overflow-hidden bg-gray-50">
            <div class="hidden sm:block sm:absolute sm:inset-y-0 sm:h-full sm:w-full">
                <div class="relative h-full max-w-screen-xl mx-auto">
                    <svg class="absolute transform right-full translate-y-1/4 translate-x-1/4 lg:translate-x-1/2" width="404" height="784" fill="none" viewBox="0 0 404 784">
                        <defs>
                            <pattern id="f210dbf6-a58d-4871-961e-36d5016a0f49" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <rect x="0" y="0" width="4" height="4" class="text-gray-200" fill="currentColor"/>
                            </pattern>
                        </defs>
                        <rect width="404" height="784" fill="url(#f210dbf6-a58d-4871-961e-36d5016a0f49)"/>
                    </svg>
                    <svg class="absolute transform left-full -translate-y-3/4 -translate-x-1/4 md:-translate-y-1/2 lg:-translate-x-1/2" width="404" height="784" fill="none" viewBox="0 0 404 784">
                        <defs>
                            <pattern id="5d0dd344-b041-4d26-bec4-8d33ea57ec9b" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                                <rect x="0" y="0" width="4" height="4" class="text-gray-200" fill="currentColor"/>
                            </pattern>
                        </defs>
                        <rect width="404" height="784" fill="url(#5d0dd344-b041-4d26-bec4-8d33ea57ec9b)"/>
                    </svg>
                </div>
            </div>

            <div class="relative pt-6 pb-12 sm:pb-16 md:pb-20 lg:pb-28 xl:pb-32">
                <div class="max-w-screen-xl px-4 mx-auto sm:px-6">
                    <nav class="relative flex items-center justify-between sm:h-10 md:justify-center">
                        <div class="flex items-center flex-1 md:absolute md:inset-y-0 md:left-0">
                            <div class="flex items-center justify-between w-full md:w-auto">

                                <div class="flex items-center -mr-2 md:hidden">
                                    <button type="button" class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                                        <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="hidden md:absolute md:flex md:items-center md:justify-end md:inset-y-0 md:right-0">
		  <span class="inline-flex rounded-md shadow">
			<a href="https://wordpress.org/plugins/utilitify" target="_blank" class="inline-flex items-center px-4 py-2 text-base font-medium leading-6 text-indigo-600 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-indigo-500 focus:outline-none focus:shadow-outline-blue active:bg-gray-50 active:text-indigo-700">
			  <?php echo 'Version: ' . KC_UF_PLUGIN_VERSION; ?>
			</a>
		  </span>
                        </div>
                    </nav>
                </div>


                <div class="absolute inset-x-0 top-0 p-2 transition origin-top-right transform md:hidden">
                    <div class="rounded-lg shadow-md">
                        <div class="overflow-hidden bg-white rounded-lg shadow-xs">
                            <div class="flex items-center justify-between px-5 pt-4">

                                <div class="-mr-2">
                                    <button type="button" class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                                        <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="max-w-screen-xl px-4 mx-auto mt-10 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 xl:mt-28">
                    <div class="text-center">
                        <h2 class="text-4xl font-extrabold leading-10 tracking-tight text-gray-900 sm:text-5xl sm:leading-none md:text-6xl">
                            Welcome to
                            <br class="xl:hidden"/>
                            <span class="text-indigo-600">Utilitify</span>
                        </h2>
                        <p class="max-w-md mx-auto mt-3 text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
							<?php _e( 'Supercharge Your WordPress Site With Powerpack WordPress Utilities', 'utilitify' ); ?>
                        </p>
                        <div class="max-w-md mx-auto mt-5 sm:flex sm:justify-center md:mt-8">
                            <div class="rounded-md shadow">
                                <a href="<?php echo $settings_page; ?>" class="flex items-center justify-center w-full px-8 py-3 text-base font-medium leading-6 text-white transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 hover:text-white focus:outline-none focus:shadow-outline-indigo md:py-4 md:text-lg md:px-10">
									<?php _e( 'Get Started', 'utilitify' ); ?>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
