<template>
    <aside class="left-panel">

        <div class="branding">

            <img
                src="/images/nstphub_logo.png"
                alt="NSTP HUB Logo"
                class="logo"
            />

            <h3>
                National Service Training Program
            </h3>

            <p class="branding-sub">
                hub
            </p>

        </div>

    </aside>
</template>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap'
);


/*
|--------------------------------------------------------------------------
| Left Branding Panel
|--------------------------------------------------------------------------
*/

.left-panel {

    width: 35%;

    max-width: 440px;

    min-width: 320px;

    min-height: 100vh;

    position: relative;

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    padding: 40px;

    background-color: #54100F;

    box-sizing: border-box;

}


/*
|--------------------------------------------------------------------------
| Branding Container
|--------------------------------------------------------------------------
*/

.branding {

    width: 100%;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

}


/*
|--------------------------------------------------------------------------
| Logo
|--------------------------------------------------------------------------
|
| SAME LOGO STYLE AS SUPER ADMIN LOGIN
|
*/

.logo {

    width: 200px;

    max-width: 80%;

    height: auto;

    display: block;

    margin:
        0
        auto
        24px;

    object-fit: contain;


    /*
    |--------------------------------------------------------------------------
    | Same White Outline / Shadow Used In Login
    |--------------------------------------------------------------------------
    */

    filter:
        drop-shadow(
            1px
            1px
            0px
            #EFEBE2
        )
        drop-shadow(
            -1px
            -1px
            0px
            #EFEBE2
        )
        drop-shadow(
            1px
            -1px
            0px
            #EFEBE2
        )
        drop-shadow(
            -1px
            1px
            0px
            #EFEBE2
        )
        drop-shadow(
            0px
            4px
            5px
            rgba(
                0,
                0,
                0,
                0.6
            )
        );

}


/*
|--------------------------------------------------------------------------
| Branding Title
|--------------------------------------------------------------------------
*/

.branding h3 {

    margin: 0;

    color: #FFFFFF;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size: 0.95rem;

    font-weight: 500;

    line-height: 1.4;

    letter-spacing: 0.5px;

}


/*
|--------------------------------------------------------------------------
| Hub Text
|--------------------------------------------------------------------------
*/

.branding-sub {

    margin:
        6px
        0
        0;

    color: #FFFFFF;

    opacity: 0.7;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size: 0.75rem;

    font-weight: 400;

    line-height: 1.4;

    text-transform: lowercase;

}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (
    max-width: 900px
) {

    .left-panel {

        display: none;

    }

}

</style>