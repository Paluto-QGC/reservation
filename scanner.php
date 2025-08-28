<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Paluto • Reservation Check-In</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@zxing/library@0.20.0"></script>
  <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
  <link rel="icon" type="image/png" href="https://i.ibb.co/0RPhcmb2/logo-pal.png" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    :root { color-scheme: dark; }
    body{font-family:'Inter',sans-serif}
    .modern-bg{background:linear-gradient(135deg,#0f172a 0%,#1f2937 25%,#334155 50%,#3f3f46 75%,#52525b 100%);min-height:100svh}
    .glass-card{background:rgba(255,255,255,.08);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.12)}
    .pill{display:inline-block;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);padding:.25rem .6rem;border-radius:9999px}
    .scan-wrap{position:relative;border-radius:14px;overflow:hidden;background:rgba(0,0,0,.25);width:100%;height:70svh;max-height:78svh}
    @media (min-width: 1024px){ .scan-wrap{ height:auto; aspect-ratio:3/4; } }
    .scan-frame{position:absolute;inset:12px;border:2px dashed rgba(255,255,255,.35);border-radius:14px;transition:.15s}
    .scan-frame.ok{border-color:#22c55e;box-shadow:inset 0 0 0 2px rgba(34,197,94,.35)}
    .scan-laser{position:absolute;left:18px;right:18px;top:50%;height:2px;background:rgba(255,255,255,.38);transform:translateY(-50%);transition:.15s}
    .scan-laser.ok{background:#22c55e}
    .status-ok{background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.35);color:#a7f3d0}
    .status-dup{background:rgba(234,179,8,.15);border-color:rgba(234,179,8,.35);color:#fde68a}
    .status-err{background:rgba(239,68,68,.15);border-color:rgba(239,68,68,.35);color:#fecaca}
  </style>
</head>
<body class="modern-bg text-white">
  <div class="w-full max-w-[980px] mx-auto px-3 sm:px-6 pt-[calc(env(safe-area-inset-top)+12px)] pb-[calc(env(safe-area-inset-bottom)+14px)]">

    <!-- Header -->
    <div class="flex items-start gap-4 sm:gap-5 mb-5">
      <img src="https://i.ibb.co/0RPhcmb2/logo-pal.png" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white/10 p-2" alt="Paluto">
      <div class="min-w-0">
        <h1 class="text-2xl sm:text-3xl font-bold">Reservation Check-In</h1>
        <p class="text-gray-300 text-sm sm:text-base">Scan the guest’s QR or type the reservation code.</p>
        <div class="mt-2 text-xs sm:text-sm text-gray-300">
          Engines:
          <button id="mBD"  class="pill">BarcodeDetector</button>
          <button id="mZX"  class="pill">ZXing</button>
          <button id="mJSQR" class="pill">jsQR</button>
        </div>
      </div>
    </div>

    <!-- Layout -->
    <div class="grid lg:grid-cols-2 gap-4 sm:gap-6">
      <!-- Scanner -->
      <section class="glass-card rounded-xl p-3 sm:p-5">
        <div class="scan-wrap">
          <video id="preview" class="absolute inset-0 w-full h-full object-cover" playsinline muted></video>
          <div id="scanFrame" class="scan-frame"></div>
          <div id="scanLaser" class="scan-laser"></div>
        </div>

        <div class="mt-3 grid grid-cols-4 gap-2">
          <button id="startBtn" class="px-3 py-3 rounded-xl font-semibold text-sm bg-gradient-to-r from-orange-500 to-red-600">Start</button>
          <button id="stopBtn"  class="px-3 py-3 rounded-xl font-semibold text-sm bg-white/10 border border-white/20" disabled>Stop</button>
          <button id="flipBtn"  class="px-3 py-3 rounded-xl font-semibold text-sm bg-white/10 border border-white/20" disabled>Flip</button>
          <button id="torchBtn" class="px-3 py-3 rounded-xl font-semibold text-sm bg-white/10 border border-white/20" disabled>Torch</button>
        </div>

        <div class="mt-3 flex items-center gap-3">
          <span class="text-xs text-gray-300">Zoom</span>
          <input id="zoom" type="range" min="1" max="1" step="0.01" value="1" class="flex-1 accent-orange-400">
        </div>

        <!-- Manual input (PLT-) -->
        <div class="mt-3 flex gap-2 items-stretch">
          <div class="flex items-center bg-white/10 border border-white/20 rounded-xl overflow-hidden flex-1">
            <span class="px-3 text-sm tracking-wider text-white/80 bg-white/10 select-none">PLT-</span>
            <input id="manualSuffix" class="flex-1 bg-transparent outline-none px-3 py-3 text-sm placeholder-gray-300"
                   placeholder="123456" autocomplete="off" inputmode="numeric" spellcheck="false" />
          </div>
          <button id="manualBtn" type="button"
            class="px-5 py-3 rounded-xl font-semibold text-sm bg-white/10 border border-white/20">
            Check In
          </button>
        </div>

        <div id="status" class="mt-3 text-sm bg-white/5 border border-white/10 rounded-xl px-3 py-2">
          Scanner ready. Aim at the code.
        </div>
        <div id="errorBox" class="mt-2 hidden text-xs border rounded-md px-3 py-2"></div>
        <pre id="last" class="mt-2 text-xs text-gray-300 bg-black/20 border border-white/10 rounded-md p-2 whitespace-pre-wrap"></pre>
      </section>

      <!-- Result -->
      <section class="glass-card rounded-xl p-3 sm:p-5">
        <h2 class="font-semibold text-lg mb-3">Last Check-In</h2>
        <div id="resultCard" class="hidden">
          <div id="badge" class="pill text-xs mb-3">Ready</div>
          <dl class="grid grid-cols-3 gap-x-4 gap-y-2 text-sm">
            <dt class="text-gray-300">Code</dt>   <dd id="rCode" class="col-span-2 font-medium break-words">—</dd>
            <dt class="text-gray-300">Name</dt>   <dd id="rName" class="col-span-2 font-medium break-words">—</dd>
            <dt class="text-gray-300">Email</dt>  <dd id="rEmail" class="col-span-2 font-medium break-words">—</dd>
            <dt class="text-gray-300">Phone</dt>  <dd id="rPhone" class="col-span-2 font-medium break-words">—</dd>
            <dt class="text-gray-300">Date</dt>   <dd id="rDate" class="col-span-2 font-medium break-words">—</dd>
            <dt class="text-gray-300">Time</dt>   <dd id="rTime" class="col-span-2 font-medium break-words">—</dd>
            <dt class="text-gray-300">Guests</dt> <dd id="rGuests" class="col-span-2 font-medium break-words">—</dd>
          </dl>
        </div>
        <p id="resultEmpty" class="text-sm text-gray-300">Awaiting first scan…</p>
      </section>
    </div>
  </div>

<script>
(() => {
  const $ = id => document.getElementById(id);
  const statusEl=$('status'), lastEl=$('last'), errBox=$('errorBox');
  const frame=$('scanFrame'), laser=$('scanLaser');
  const card=$('resultCard'), empty=$('resultEmpty'), badge=$('badge');
  const video=$('preview'), startBtn=$('startBtn'), stopBtn=$('stopBtn');
  const flipBtn=$('flipBtn'), torchBtn=$('torchBtn'), zoomRange=$('zoom');
  const mBD=$('mBD'), mZX=$('mZX'), mJSQR=$('mJSQR');
  const manualSuffix=$('manualSuffix'), manualBtn=$('manualBtn');

  let stream=null, track=null, imageCapture=null;
  let devices=[], deviceIndex=0, scanning=false, rafId=0;
  let bdDetector=null, zxingReader=null, mode='bd';
  let lastCode='', lastHitAt=0; const hitCooldownMs=1200;

  const off=document.createElement('canvas'); const offCtx=off.getContext('2d');

  function setStatus(t, type=''){ statusEl.textContent=t;
    statusEl.classList.remove('status-ok','status-dup','status-err');
    if(type==='ok')statusEl.classList.add('status-ok');
    if(type==='dup')statusEl.classList.add('status-dup');
    if(type==='err')statusEl.classList.add('status-err');
  }
  function showError(m){ errBox.classList.remove('hidden'); errBox.classList.add('status-err'); errBox.textContent=m; }
  function clearError(){ errBox.classList.add('hidden'); errBox.textContent=''; }
  function flashOk(){ frame.classList.add('ok'); laser.classList.add('ok'); setTimeout(()=>{frame.classList.remove('ok'); laser.classList.remove('ok');},600); }
  const val = v => { v=(v??'').toString().trim(); return v.length?v:'—'; };

  function fillResult(data){
    empty.classList.add('hidden'); card.classList.remove('hidden');
    $('rCode').textContent   = val(data.code);
    $('rName').textContent   = val(data.name);
    $('rEmail').textContent  = val(data.email);
    $('rPhone').textContent  = val(data.phone);
    $('rDate').textContent   = val(data.date);
    $('rTime').textContent   = val(data.time);
    $('rGuests').textContent = val(data.guests);
    badge.classList.remove('status-ok','status-dup','status-err');
    if (data.status==='ok' && data.already){ badge.textContent='Already Checked-In'; badge.classList.add('status-dup'); }
    else if (data.status==='ok'){ badge.textContent='Checked-In'; badge.classList.add('status-ok'); }
    else { badge.textContent='Error'; badge.classList.add('status-err'); }
  }

  function extractCode(raw){
    // Try JSON payload from your QR: { resNo: "PLT-123456", ... }
    try { const o=JSON.parse(raw); if (o && o.resNo) return String(o.resNo); } catch(_) {}
    // Fallback: find PLT-###### in raw text
    const m = /PLT-\d{6}/i.exec(raw);
    return m ? m[0].toUpperCase() : raw.trim();
  }

  async function checkIn(code){
    clearError();
    setStatus('Checking in '+code+' …');
    try{
      const res = await fetch('checkin.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ code }) });
      const text = await res.text();
      let data; try { data = JSON.parse(text); } catch(e){ showError('Parse error: '+e.message); lastEl.textContent='Raw:\n'+text; return; }
      fillResult(data);
      if (data.status==='ok') setStatus('✅ '+code+' • '+val(data.checkedInAt),'ok');
      else setStatus('❌ '+val(data.message),'err');
    }catch(err){ showError(err.message); }
  }

  function handleDecoded(raw){
    lastEl.textContent = raw ? 'Raw:\n'+raw : '';
    const code = extractCode(raw).toUpperCase();
    manualSuffix.value = code.replace(/^PLT-?/i,'').replace(/\D/g,'').slice(0,6);
    if (!/^PLT-\d{6}$/.test(code)){ setStatus('Scanned, but not a valid code. Move closer or reduce glare.','err'); return; }
    const now=performance.now(); if (code===lastCode && now-lastHitAt<hitCooldownMs) return;
    lastCode=code; lastHitAt=now; flashOk(); checkIn(code);
  }

  async function listCameras(){ try{await navigator.mediaDevices.getUserMedia({video:true,audio:false});}catch(_){} devices=(await navigator.mediaDevices.enumerateDevices()).filter(d=>d.kind==='videoinput'); flipBtn.disabled=devices.length<=1; }
  async function startCamera(deviceId){
    stopCamera();
    const constraints={audio:false,video:{deviceId:deviceId?{exact:deviceId}:undefined,facingMode:deviceId?undefined:{ideal:'environment'},width:{ideal:1280},height:{ideal:720}}};
    stream=await navigator.mediaDevices.getUserMedia(constraints); video.srcObject=stream; await video.play();
    track=stream.getVideoTracks()[0];
    const caps=track.getCapabilities?.()||{}; torchBtn.disabled=!caps.torch; if('torch' in caps) imageCapture=new ImageCapture(track);
    const zMin=caps.zoom?.min??1, zMax=caps.zoom?.max??1; zoomRange.min=zMin; zoomRange.max=zMax; zoomRange.step=caps.zoom?.step??0.01; zoomRange.value=track.getSettings?.().zoom??zMin; zoomRange.disabled=zMin===zMax;
    setStatus('Scanner running. Aim at the code.');
  }
  function stopCamera(){ if(stream){stream.getTracks().forEach(t=>t.stop()); stream=null;} track=null; imageCapture=null; }

  async function startScanner(){
    if (mode==='bd'){ if(!('BarcodeDetector' in window)){ mode='zxing'; return startScanner(); } if(!bdDetector) bdDetector=new BarcodeDetector({formats:['qr_code','code_128']}); loopBD(); }
    else if (mode==='zxing'){ if(!zxingReader){ const hints=new Map(); hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS,[ZXing.BarcodeFormat.QR_CODE,ZXing.BarcodeFormat.CODE_128]); hints.set(ZXing.DecodeHintType.TRY_HARDER,true); zxingReader=new ZXing.BrowserMultiFormatReader(hints); }
      await zxingReader.decodeFromVideoDevice(devices[deviceIndex]?.deviceId, video, (result, err)=>{ if(result) handleDecoded(result.getText()); }); }
    else { loopJSQR(); }
  }
  function stopScanner(){ if(rafId) cancelAnimationFrame(rafId); try{ zxingReader?.reset(); }catch(_){} }

  async function loopBD(){ if(!video.videoWidth){ rafId=requestAnimationFrame(loopBD); return; } const w=640,h=Math.round(640*(video.videoHeight/video.videoWidth||0.75)); off.width=w; off.height=h; offCtx.drawImage(video,0,0,w,h);
    try{ const codes = await bdDetector.detect(off); if(codes.length) handleDecoded(codes[0].rawValue||''); }catch(_){}
    rafId=requestAnimationFrame(loopBD);
  }
  async function loopJSQR(){ if(!video.videoWidth){ rafId=requestAnimationFrame(loopJSQR); return; } const w=640,h=Math.round(640*(video.videoHeight/video.videoWidth||0.75)); off.width=w; off.height=h; offCtx.drawImage(video,0,0,w,h);
    try{ const img=offCtx.getImageData(0,0,w,h); const code=jsQR(img.data,w,h,{inversionAttempts:'attemptBoth'}); if(code?.data) handleDecoded(code.data);}catch(_){}
    rafId=requestAnimationFrame(loopJSQR);
  }

  zoomRange.addEventListener('input', async e=>{ if(!track) return; try{ await track.applyConstraints({advanced:[{zoom:Number(e.target.value)}]}); }catch(_){} });
  torchBtn.addEventListener('click', async ()=>{ if(!track) return; const caps=track.getCapabilities?.()||{}; if(!caps.torch) return; const cur=track.getSettings?.().torch||false; try{ await track.applyConstraints({advanced:[{torch:!cur}]}); torchBtn.textContent=!cur?'Torch On':'Torch'; }catch(_){ } });

  startBtn.addEventListener('click', async ()=>{ startBtn.disabled=true; stopBtn.disabled=false; await listCameras(); await startCamera(devices[deviceIndex]?.deviceId); await startScanner(); });
  stopBtn.addEventListener('click', ()=>{ stopBtn.disabled=true; startBtn.disabled=false; stopScanner(); stopCamera(); setStatus('Stopped.'); });
  flipBtn.addEventListener('click', async ()=>{ if(devices.length<=1) return; deviceIndex=(deviceIndex+1)%devices.length; await startCamera(devices[deviceIndex].deviceId); stopScanner(); await startScanner(); });

  function setModeButtons(){ for(const el of [mBD,mZX,mJSQR]) el.classList.remove('ring-2','ring-orange-400'); (mode==='bd'?mBD:mode==='zxing'?mZX:mJSQR).classList.add('ring-2','ring-orange-400'); }
  mBD.onclick=async()=>{ mode='bd'; setModeButtons(); stopScanner(); await startScanner(); };
  mZX.onclick=async()=>{ mode='zxing'; setModeButtons(); stopScanner(); await startScanner(); };
  mJSQR.onclick=async()=>{ mode='jsqr'; setModeButtons(); stopScanner(); await startScanner(); };

  manualSuffix.addEventListener('input', e=>{ e.target.value = e.target.value.replace(/\D/g,'').slice(0,6); });
  manualBtn.addEventListener('click', ()=>{ const suffix=manualSuffix.value.trim(); const code='PLT-'+suffix.padStart(6,'0'); if(!/^PLT-\d{6}$/.test(code)){ setStatus('Enter a valid 6-digit code.','err'); return; } handleDecoded(code); });

  (async()=>{ mode=('BarcodeDetector' in window)?'bd':'zxing'; setModeButtons(); await listCameras(); startBtn.click(); })();
})();
</script>
</body>
</html>
