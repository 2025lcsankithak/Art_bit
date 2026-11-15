function authMode(mode,el){document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));el.classList.add('active');let f=document.querySelector('#authform');f.querySelector('[name=action]').value=mode;f.querySelector('.name-field').hidden=mode==='login';f.querySelector('.role-field').hidden=mode==='login';}
function setBid(add){let i=document.querySelector('[name=amount]');if(i)i.value=Number(i.min)+add-100;}
document.querySelectorAll('.timer').forEach(el=>{
 let end=el.dataset.endEpoch?Number(el.dataset.endEpoch):new Date(el.dataset.end.replace(' ','T')).getTime();
 function tick(){let n=Math.max(0,end-Date.now()),h=Math.floor(n/36e5),m=Math.floor(n%36e5/6e4),s=Math.floor(n%6e4/1e3);el.textContent=[h,m,s].map(x=>String(x).padStart(2,'0')).join(':');}
 tick();setInterval(tick,1000);
});
