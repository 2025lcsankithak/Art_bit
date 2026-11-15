function authMode(mode,el){document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));el.classList.add('active');let f=document.querySelector('#authform');f.querySelector('[name=action]').value=mode;f.querySelector('.name-field').hidden=mode==='login';f.querySelector('.role-field').hidden=mode==='login';}
function setBid(add){let i=document.querySelector('[name=amount]');if(i)i.value=Number(i.dataset.currentBid)+add;}
document.querySelectorAll('[data-upi-toggle]').forEach(button=>{
 let panel=document.getElementById(button.getAttribute('aria-controls'));
 let hideTimer;
 function hide(){panel.hidden=true;button.setAttribute('aria-expanded','false');button.textContent='Pay now';clearTimeout(hideTimer);}
 button.addEventListener('click',()=>{
  if(!panel.hidden){hide();return;}
  panel.hidden=false;button.setAttribute('aria-expanded','true');button.textContent='Hide QR';
  const qr=panel.querySelector('[data-upi-uri]');
  if(qr&&!qr.dataset.generated&&window.QRCode){new QRCode(qr,{text:qr.dataset.upiUri,width:180,height:180,colorDark:'#20231f',colorLight:'#ffffff',correctLevel:QRCode.CorrectLevel.M});qr.dataset.generated='true';}
  hideTimer=setTimeout(hide,10000);
 });
});
document.querySelectorAll('.timer').forEach(el=>{
 let end=el.dataset.endEpoch?Number(el.dataset.endEpoch):new Date(el.dataset.end.replace(' ','T')).getTime();
 function tick(){let n=Math.max(0,end-Date.now()),h=Math.floor(n/36e5),m=Math.floor(n%36e5/6e4),s=Math.floor(n%6e4/1e3);el.textContent=[h,m,s].map(x=>String(x).padStart(2,'0')).join(':');}
 tick();setInterval(tick,1000);
});
