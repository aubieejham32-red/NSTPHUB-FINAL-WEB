<template>
  <div class="left-panel">
    <div class="branding">
      <img
        src="/images/nstphub_logo.png"
        alt="NSTP HUB Logo"
        class="logo"
      />

      <h3>National Service Training Program</h3>

      <p class="branding-sub">
        hub
      </p>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

/* =====================
    LEFT PANEL
===================== */

.left-panel {
  width: 35%;
  max-width: 440px;
  min-width: 320px;

  background:
    radial-gradient(circle at top,
      #76962A 0%,
      #648321 35%,
      #58761C 70%,
      #4D6818 100%);

  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px;
  box-sizing: border-box;
}

/* =====================
    BRANDING
===================== */

.branding {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.branding::before {
  content: "";
  position: absolute;
  top: 8px;
  width: 260px;
  height: 260px;
  border-radius: 50%;

  background: radial-gradient(
      circle,
      rgba(255, 225, 120, 0.22) 0%,
      rgba(255, 225, 120, 0.10) 38%,
      rgba(255, 225, 120, 0.03) 62%,
      transparent 82%
  );

  filter: blur(12px);
  z-index: 0;
}

.logo {
  width: 225px;
  height: auto;
  margin-bottom: 30px;

  filter:
    drop-shadow(1px 0 0 #fff)
    drop-shadow(-1px 0 0 #fff)
    drop-shadow(0 1px 0 #fff)
    drop-shadow(0 -1px 0 #fff)
    drop-shadow(1px 1px 0 #fff)
    drop-shadow(-1px -1px 0 #fff)
    drop-shadow(1px -1px 0 #fff)
    drop-shadow(-1px 1px 0 #fff)

    drop-shadow(0 0 10px rgba(255,255,255,.45))
    drop-shadow(0 0 18px rgba(255,214,102,.30))
    drop-shadow(0 10px 18px rgba(0,0,0,.25));
}

.branding h3 {
  margin: 0;
  color: #FFFFFF;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.95rem;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.branding-sub {
  margin-top: 6px;
  color: #FFFFFF;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.75rem;
  font-weight: 400;
  opacity: 0.7;
  text-transform: lowercase;
  letter-spacing: 1px;
}

/* =====================
    RESPONSIVE
===================== */

@media (max-width: 900px) {
  .left-panel {
    display: none;
  }
}
</style>