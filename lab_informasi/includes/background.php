<style>
    body::before {
        content: '';
        background-image: url('../assets/images/bg1.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        filter: blur(2px);
        -webkit-filter: blur(2px);
    }

    .content {
        position: relative;
        z-index: 1;
        background-color: rgba(255, 255, 255, 0.8);
        min-height: 100vh;
    }
</style>
<div class="content">
</div>
