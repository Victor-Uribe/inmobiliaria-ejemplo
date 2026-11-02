<template>
    <transition name="loader-fade">
        <div v-if="show" class="page-loader">
            <div class="loader-blur"></div>

            <div class="loader-animation">
                <div class="loader-ring ring-one"></div>
                <div class="loader-ring ring-two"></div>
                <div class="loader-core"></div>

                <div class="loader-particles">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'PageLoader',

    props: {
        show: {
            type: Boolean,
            default: false
        }
    }
}
</script>

<style scoped>
.page-loader {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(5 5 5 / 90%);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    overflow: hidden;
}

.loader-blur {
    position: absolute;
    width: 320px;
    height: 320px;
    border-radius: 999px;
    background: rgba(244, 212, 135, .16);
    filter: blur(70px);
    animation: loaderGlow 2.8s ease-in-out infinite;
}

.loader-animation {
    position: relative;
    width: 118px;
    height: 118px;
    display: grid;
    place-items: center;
}

.loader-ring {
    position: absolute;
    border-radius: 999px;
    border: 1px solid rgba(244, 212, 135, .18);
}

.ring-one {
    inset: 0;
    border-top-color: rgba(244, 212, 135, 1);
    border-right-color: rgba(244, 212, 135, .55);
    animation: spinLoader 1.15s linear infinite;
}

.ring-two {
    inset: 18px;
    border-bottom-color: rgba(244, 212, 135, .95);
    border-left-color: rgba(244, 212, 135, .45);
    animation: spinLoaderReverse 1.4s linear infinite;
}

.loader-core {
    margin-top: 25px;
    width: 42px;
    height: 42px;
    border-radius: 100%;
    background:
        linear-gradient(145deg, #f7dc92 0%, #c69a42 58%, #7f5b1e 100%);
    box-shadow:
        0 0 32px rgba(244, 212, 135, .42),
        inset 0 1px 0 rgba(255, 255, 255, .55);
    animation: corePulse 1.25s ease-in-out infinite;
}

.loader-particles span {
    position: absolute;
    width: 7px;
    height: 7px;
    border-radius: 999px;
    background: #f4d487;
    box-shadow: 0 0 18px rgba(244, 212, 135, .9);
}

.loader-particles span:nth-child(1) {
    top: 4px;
    left: 50%;
    transform: translateX(-50%);
    animation: particlePulse 1.2s ease-in-out infinite;
}

.loader-particles span:nth-child(2) {
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    animation: particlePulse 1.2s ease-in-out .15s infinite;
}

.loader-particles span:nth-child(3) {
    bottom: 4px;
    left: 50%;
    transform: translateX(-50%);
    animation: particlePulse 1.2s ease-in-out .3s infinite;
}

.loader-particles span:nth-child(4) {
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    animation: particlePulse 1.2s ease-in-out .45s infinite;
}

.loader-fade-enter-active,
.loader-fade-leave-active {
    transition: opacity .35s ease;
}

.loader-fade-enter,
.loader-fade-leave-to {
    opacity: 0;
}

@keyframes spinLoader {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

@keyframes spinLoaderReverse {
    from {
        transform: rotate(360deg);
    }

    to {
        transform: rotate(0deg);
    }
}

@keyframes corePulse {

    0%,
    100% {
        transform: scale(1);
        opacity: .9;
    }

    50% {
        transform: scale(1.12);
        opacity: 1;
    }
}

@keyframes particlePulse {

    0%,
    100% {
        opacity: .25;
        transform: scale(.65);
    }

    50% {
        opacity: 1;
        transform: scale(1.25);
    }
}

@keyframes loaderGlow {

    0%,
    100% {
        transform: scale(.9);
        opacity: .55;
    }

    50% {
        transform: scale(1.18);
        opacity: .9;
    }
}

@media (max-width: 575px) {
    .loader-animation {
        width: 96px;
        height: 96px;
    }

    .ring-two {
        inset: 15px;
    }

    .loader-core {
        width: 36px;
        height: 36px;
        border-radius: 14px;
    }
}
</style>