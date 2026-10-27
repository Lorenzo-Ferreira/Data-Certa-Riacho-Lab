<svg viewBox="0 0 480 420" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" role="img" aria-label="Ilustração de uma sacola de compras com pão, maçã e alface, e uma etiqueta de desconto">
  <defs>
    <clipPath id="hi-bag-clip">
      <path d="M120 168 L360 168 L340 372 Q338 388 322 388 L158 388 Q142 388 140 372 Z"/>
    </clipPath>
  </defs>

  <ellipse cx="240" cy="392" rx="150" ry="18" fill="rgba(0,0,0,.14)"/>

  <circle cx="240" cy="196" r="178" fill="rgba(255,255,255,.10)"/>
  <circle cx="356" cy="96" r="30" fill="rgba(255,255,255,.14)"/>
  <circle cx="82" cy="300" r="22" fill="rgba(255,255,255,.12)"/>

  <!-- alça da sacola -->
  <path d="M172 168 Q172 92 240 92 Q308 92 308 168" fill="none" stroke="#FFF3E0" stroke-width="14" stroke-linecap="round"/>
  <path d="M172 168 Q172 92 240 92 Q308 92 308 168" fill="none" stroke="#C62828" stroke-width="6" stroke-linecap="round" opacity=".35"/>

  <!-- corpo da sacola -->
  <path d="M120 168 L360 168 L340 372 Q338 388 322 388 L158 388 Q142 388 140 372 Z" fill="#FFF8F0" stroke="#C62828" stroke-width="6" stroke-linejoin="round"/>
  <path d="M120 168 L360 168 L353 220 L127 220 Z" fill="#E53935"/>

  <g clip-path="url(#hi-bag-clip)">
    <!-- pão -->
    <ellipse cx="205" cy="300" rx="62" ry="40" fill="#E8A452" stroke="#B9772C" stroke-width="4"/>
    <path d="M172 292 Q205 270 238 292" fill="none" stroke="#B9772C" stroke-width="4" stroke-linecap="round"/>
    <path d="M178 306 Q205 288 232 306" fill="none" stroke="#B9772C" stroke-width="4" stroke-linecap="round"/>

    <!-- maçã -->
    <circle cx="300" cy="312" r="34" fill="#2FAE68" stroke="#1E7E4C" stroke-width="4"/>
    <path d="M300 280 Q296 266 306 258" fill="none" stroke="#6B4226" stroke-width="5" stroke-linecap="round"/>
    <path d="M306 262 Q322 254 330 264" fill="#3FBE78" stroke="#1E7E4C" stroke-width="3"/>

    <!-- folha de alface -->
    <path d="M150 340 Q168 300 214 306 Q206 344 168 356 Q152 360 150 340Z" fill="#68C58A" stroke="#2E9A5C" stroke-width="4"/>
  </g>

  <!-- etiqueta de desconto -->
  <g transform="translate(350 250) rotate(14)">
    <path d="M0 -34 L34 -20 L34 20 L0 34 L-34 20 L-34 -20 Z" fill="#E53935" stroke="#FFF3E0" stroke-width="5"/>
    <circle cx="0" cy="-16" r="4" fill="#FFF3E0"/>
    <text x="0" y="10" text-anchor="middle" font-family="'Plus Jakarta Sans', sans-serif" font-size="17" font-weight="800" fill="#FFF3E0">-50%</text>
  </g>

  <!-- folhinhas soltas -->
  <g fill="none" stroke="rgba(255,255,255,.75)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
    <path d="M96 150 c-3 -8 0 -15 8 -18 4 5 4 12 0 17 c-2 3 -6 3 -8 1Z"/>
    <path d="M392 190 c-3 -8 0 -15 8 -18 4 5 4 12 0 17 c-2 3 -6 3 -8 1Z"/>
  </g>
</svg>
