<template>
  <header class="header-bar">
    <div class="header-left">
      <img
        src="/images/nstphub_logo.png"
        alt="NSTP HUB Logo"
        class="header-logo"
      />
      <div class="header-text">
        <h2>National Service Training Program</h2>
        <p>hub</p>
      </div>
    </div>
  </header>
</template>

<style scoped>
.header-bar {
  background-color: #510D0A; /* Dark Maroon */
  height: 110px;
  width: 100%;
  display: flex;
  align-items: center;
  padding: 0 48px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
  box-sizing: border-box;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.header-logo {
  height: 80px;
  width: auto;
  object-fit: contain;
  filter: 
    drop-shadow(0 0 2px rgba(255, 255, 255, 0.6));
}

.header-text h2 {
  color: #FFFFFF;
  font-size: 1.1rem;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.5px;
}

.header-text p {
  color: #FFFFFF;
  font-size: 0.8rem;
  font-weight: 400;
  margin: 4px 0 0 0;
  opacity: 0.7;
  text-transform: lowercase;
}

@media (max-width: 768px) {
  .header-bar {
    padding: 0 24px;
  }
}
</style>