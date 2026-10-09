import{E as e,F as t,I as n,M as r,O as i,U as a,a as o,g as s,h as c,t as l,v as u,w as d,x as f,y as p,z as m}from"./app-CbbAkQIW.js";import{t as h}from"./browser-C9OrB-0c.js";var g={class:`mb-6 pb-4 border-b-2 border-amber-600 print-kop-container text-slate-900`},_={class:`flex items-center justify-between gap-4`},v={class:`flex items-center space-x-4`},y={class:`w-16 h-16 rounded-xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-xs`},b=[`src`,`alt`],x={class:`text-base font-black text-gray-900 uppercase tracking-tight leading-tight`},S={class:`text-[9.5px] text-gray-600 mt-0.5 leading-normal`},C={class:`text-right shrink-0`},w={class:`text-[9.5px] text-gray-500 mt-1`},T={class:`font-medium text-gray-700`},E={class:`mt-2 bg-slate-50 border border-slate-200 rounded-lg p-3 flex justify-between items-center text-gray-900`},D={class:`text-xs font-black uppercase tracking-wider text-gray-900`},O={key:0,class:`text-[10px] text-gray-600 font-medium mt-0.5`},k={class:`text-right`},A={class:`text-[9.5px] font-mono font-bold bg-white px-2 py-0.5 border border-slate-300 rounded text-slate-800`},j={__name:`FinancialPrintHeader`,props:{title:{type:String,required:!0},subtitle:{type:String,default:``},period:{type:String,default:``},docNumber:{type:String,default:``}},setup(t){let r=o(),i=c(()=>r?.props?.mktProfile||{}),a=c(()=>i.value?.logo?i.value.logo:l),d=c(()=>i.value?.name||`YAYASAN MITRA KEMANUSIAAN TERPADU (MKT)`),h=c(()=>i.value?.address||`Jl. Ujung Pandang No. 45, Kota Makassar, Sulawesi Selatan`),j=c(()=>i.value?.phone||`+62 812-3456-7890`),M=c(()=>i.value?.email||`info@mkt.or.id`),N=new Date().toLocaleDateString(`id-ID`,{weekday:`long`,year:`numeric`,month:`long`,day:`numeric`});return(r,i)=>(e(),p(`div`,g,[s(`div`,_,[s(`div`,v,[s(`div`,y,[s(`img`,{src:a.value,alt:d.value,class:`w-full h-full object-contain`},null,8,b)]),s(`div`,null,[s(`h1`,x,m(d.value),1),i[0]||=s(`p`,{class:`text-[11px] font-bold text-amber-700 leading-tight mt-0.5`},` Ekosistem Penanggulangan Bencana, Tim Rescue & Relawan Donor Darah `,-1),s(`p`,S,m(h.value)+` | Hotline: `+m(j.value)+` | Email: `+m(M.value),1)])]),s(`div`,C,[i[2]||=s(`span`,{class:`inline-block px-2.5 py-1 bg-amber-50 text-amber-900 text-[9px] font-extrabold uppercase rounded border border-amber-300`},` Dokumen Keuangan Resmi `,-1),s(`p`,w,[i[1]||=f(` Tgl Cetak: `,-1),s(`span`,T,m(n(N)),1)])])]),i[3]||=s(`div`,{class:`mt-3 pt-2 border-t border-amber-500/40`},null,-1),s(`div`,E,[s(`div`,null,[s(`h2`,D,m(t.title),1),t.period||t.subtitle?(e(),p(`p`,O,m(t.period?`Periode Laporan: ${t.period}`:t.subtitle),1)):u(``,!0)]),s(`div`,k,[s(`span`,A,m(t.docNumber||`Double-Entry Verified`),1)])])]))}},M=a(h()),N={class:`mt-8 pt-4 border-t border-slate-200 text-gray-900 break-inside-avoid`},P={class:`text-right text-[10px] text-gray-700 font-medium mb-4`},F={class:`grid grid-cols-2 gap-8 text-center text-xs`},I={class:`text-[10px] text-gray-600 mb-16`},L={class:`font-bold text-gray-900 border-b border-gray-400 inline-block px-4 pb-0.5 min-w-[180px]`},R={class:`text-[9px] text-gray-500 mt-1`},z={class:`text-[10px] text-gray-600 mb-16`},B={class:`font-bold text-gray-900 border-b border-gray-400 inline-block px-4 pb-0.5 min-w-[180px]`},V={class:`text-[9px] text-gray-500 mt-1`},H={class:`mt-8 pt-4 border-t-2 border-slate-200 flex items-center justify-between gap-4 text-slate-800`},U={class:`flex items-center space-x-3 text-left`},W={class:`w-16 h-16 p-1 bg-white border border-slate-300 rounded-lg shrink-0 flex items-center justify-center shadow-xs`},G=[`src`],K={key:1,class:`text-[8px] text-slate-400 font-mono`},q={class:`leading-tight`},J={class:`font-mono text-[8.5px] text-amber-800 font-bold mt-0.5`},Y={class:`text-right text-[8.5px] text-slate-500 leading-tight shrink-0`},X={class:`font-mono text-[8px] text-slate-600 mt-0.5`},Z={__name:`FinancialPrintSignatures`,props:{location:{type:String,default:`Makassar`},docNumber:{type:String,default:``},signer1Title:{type:String,default:``},signer1Name:{type:String,default:``},signer1Nip:{type:String,default:``},signer2Title:{type:String,default:``},signer2Name:{type:String,default:``},signer2Nip:{type:String,default:``}},setup(i){let a=i,l=o(),u=c(()=>l?.props?.organizationLeaders||{}),f=c(()=>a.signer1Name||u.value?.chairman?.name||`Salman Hasmin, ST`),h=c(()=>a.signer1Title||`Ketua Pengurus Yayasan MKT`),g=c(()=>a.signer1Nip||u.value?.chairman?.nip||`NIP/KTA: MKT-PG-001`),_=c(()=>a.signer2Name||u.value?.treasurer?.name||`Sarif, ST`),v=c(()=>a.signer2Title||u.value?.treasurer?.title||`Bendahara Umum`),y=c(()=>a.signer2Nip||u.value?.treasurer?.nip||`NIP/KTA: MKT-PG-003`),b=new Date().toLocaleDateString(`id-ID`,{day:`numeric`,month:`long`,year:`numeric`}),x=t(``),S=c(()=>a.docNumber&&a.docNumber!==`Double-Entry Verified`?a.docNumber:`MKT-VAL-${new Date().getFullYear()}${String(new Date().getMonth()+1).padStart(2,`0`)}-FIN88`),C=c(()=>`MKT-FIN-${new Date().getFullYear()}-VERIFIED`),w=async()=>{try{let e=`https://mkt.or.id/verify-report?doc=${encodeURIComponent(S.value)}&auth=Salman+Hasmin&org=Yayasan+MKT`;x.value=await M.toDataURL(e,{width:160,margin:1,color:{dark:`#0f172a`,light:`#ffffff`}})}catch(e){console.error(`Failed to generate QR Code:`,e)}};return d(()=>{w()}),r(()=>a.docNumber,()=>{w()}),(t,r)=>(e(),p(`div`,N,[s(`div`,P,m(i.location)+`, `+m(n(b)),1),s(`div`,F,[s(`div`,null,[r[0]||=s(`p`,{class:`font-bold text-gray-800 text-[11px]`},`Mengetahui,`,-1),s(`p`,I,m(h.value),1),s(`p`,L,` ( `+m(f.value)+` ) `,1),s(`p`,R,m(g.value),1)]),s(`div`,null,[r[1]||=s(`p`,{class:`font-bold text-gray-800 text-[11px]`},`Dibuat & Divalidasi Oleh,`,-1),s(`p`,z,m(v.value),1),s(`p`,B,` ( `+m(_.value)+` ) `,1),s(`p`,V,m(y.value),1)])]),s(`div`,H,[s(`div`,U,[s(`div`,W,[x.value?(e(),p(`img`,{key:0,src:x.value,alt:`QR Code Validasi Dokumen`,class:`w-full h-full object-contain`},null,8,G)):(e(),p(`div`,K,`QR Code`))]),s(`div`,q,[r[2]||=s(`div`,{class:`flex items-center space-x-1.5 font-black text-[9.5px] text-slate-900 uppercase tracking-tight`},[s(`span`,{class:`w-2 h-2 rounded-full bg-emerald-500 inline-block`}),s(`span`,null,`Dokumen Digital Sah & Terverifikasi`)],-1),s(`p`,J,` No. Validasi: `+m(S.value),1),r[3]||=s(`p`,{class:`text-[8px] text-slate-500 mt-0.5 max-w-[260px]`},` Pindai QR Code untuk verifikasi keaslian dokumen resmi Yayasan MKT. `,-1)])]),s(`div`,Y,[r[4]||=s(`p`,{class:`font-bold text-slate-700`},`Sistem Informasi Akuntansi Yayasan MKT`,-1),s(`p`,X,`Security Hash: `+m(C.value),1),r[5]||=s(`p`,{class:`italic text-[7.5px] text-slate-400 mt-0.5`},`Dihasilkan secara terkomputerisasi tanpa stempel basah.`,-1)])])]))}},Q={key:0,class:`fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-start justify-center p-4 sm:p-6 print:p-0 print:m-0 print:static print:bg-white print:overflow-visible print:block print-modal-container`},$={class:`bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-800 w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden my-6 flex flex-col max-h-[90vh] print:max-h-none print:my-0 print:shadow-none print:border-none print:rounded-none print:w-full print:max-w-full print:overflow-visible`},ee={class:`h-14 px-6 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0 print:hidden`},te={class:`flex items-center space-x-2.5`},ne={class:`text-sm font-bold text-white tracking-tight`},re={class:`flex items-center space-x-2`},ie={class:`flex-1 overflow-y-auto p-6 sm:p-10 bg-slate-200 dark:bg-slate-950/80 flex justify-center print:p-0 print:m-0 print:bg-white print:overflow-visible`},ae={class:`px-6 py-3 bg-slate-100 dark:bg-gray-900 border-t border-slate-200 dark:border-gray-800 flex justify-between items-center text-xs text-slate-500 dark:text-gray-400 shrink-0 print:hidden`},oe={__name:`FinancialPrintModal`,props:{show:{type:Boolean,default:!1},title:{type:String,default:`Pratinjau Cetak Laporan Keuangan (Mode Terang)`}},emits:[`close`],setup(n,{emit:r}){let a=n,o=r,c=t(null),l=()=>{if(!c.value){window.print();return}let e=document.createElement(`iframe`);e.style.position=`fixed`,e.style.right=`0`,e.style.bottom=`0`,e.style.width=`0`,e.style.height=`0`,e.style.border=`none`,document.body.appendChild(e);let t=e.contentWindow.document;t.open(),t.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>${a.title||`Laporan Keuangan Yayasan MKT`}</title>
            <style>
                @page {
                    size: A4 portrait;
                    margin: 12mm 15mm;
                }
                *, *::before, *::after {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }
                body {
                    background: #ffffff !important;
                    color: #0f172a !important;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    font-size: 11px;
                    line-height: 1.4;
                    padding: 4px;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                    margin-bottom: 12px;
                    font-size: 10px;
                    page-break-inside: auto;
                }
                tr {
                    page-break-inside: avoid;
                    page-break-after: auto;
                }
                thead {
                    display: table-header-group;
                }
                th, td {
                    border: 1px solid #cbd5e1;
                    padding: 6px 8px;
                    color: #0f172a;
                }
                th {
                    background-color: #f1f5f9 !important;
                    font-weight: 800;
                    text-transform: uppercase;
                    font-size: 9px;
                    letter-spacing: 0.05em;
                }
                tbody tr:nth-child(even) td {
                    background-color: #f8fafc;
                }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .text-left { text-align: left; }
                .font-bold { font-weight: bold; }
                .font-black { font-weight: 900; }
                .font-medium { font-weight: 500; }
                .font-semibold { font-weight: 600; }
                .font-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
                .uppercase { text-transform: uppercase; }
                .flex { display: flex; }
                .justify-between { justify-content: space-between; }
                .items-center { align-items: center; }
                .space-x-4 > * + * { margin-left: 1rem; }
                .space-x-3 > * + * { margin-left: 0.75rem; }
                .space-x-2 > * + * { margin-left: 0.5rem; }
                .space-x-1 > * + * { margin-left: 0.25rem; }
                .space-y-1\\.5 > * + * { margin-top: 0.375rem; }
                .space-y-2 > * + * { margin-top: 0.5rem; }
                .space-y-3 > * + * { margin-top: 0.75rem; }
                .grid { display: grid; }
                .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .gap-2 { gap: 0.5rem; }
                .gap-4 { gap: 1rem; }
                .gap-8 { gap: 2rem; }
                .border-b-2 { border-bottom-width: 2px; }
                .border-b { border-bottom-width: 1px; }
                .border-t-2 { border-top-width: 2px; }
                .border-t { border-top-width: 1px; }
                .border-amber-600 { border-color: #d97706; }
                .border-amber-500 { border-color: #f59e0b; }
                .border-amber-300 { border-color: #fcd34d; }
                .border-slate-300 { border-color: #cbd5e1; }
                .border-slate-200 { border-color: #e2e8f0; }
                .border-slate-100 { border-color: #f1f5f9; }
                .border-gray-400 { border-color: #9ca3af; }
                .border { border: 1px solid #cbd5e1; }
                .text-amber-700 { color: #b45309; }
                .text-amber-800 { color: #92400e; }
                .text-amber-900 { color: #78350f; }
                .text-emerald-700 { color: #047857; }
                .text-emerald-800 { color: #065f46; }
                .text-slate-900 { color: #0f172a; }
                .text-slate-800 { color: #1e293b; }
                .text-slate-700 { color: #334155; }
                .text-slate-600 { color: #475569; }
                .text-slate-500 { color: #64748b; }
                .text-gray-900 { color: #0f172a; }
                .text-gray-800 { color: #1e293b; }
                .text-gray-700 { color: #334155; }
                .text-gray-600 { color: #475569; }
                .text-gray-500 { color: #64748b; }
                .bg-slate-50 { background-color: #f8fafc; }
                .bg-slate-100 { background-color: #f1f5f9; }
                .bg-amber-50 { background-color: #fffbeb; }
                .bg-amber-100 { background-color: #fef3c7; }
                .bg-emerald-50 { background-color: #ecfdf5; }
                .bg-white { background-color: #ffffff; }
                .rounded { border-radius: 0.25rem; }
                .rounded-lg { border-radius: 0.5rem; }
                .rounded-xl { border-radius: 0.75rem; }
                .p-1 { padding: 0.25rem; }
                .p-2 { padding: 0.5rem; }
                .p-2\\.5 { padding: 0.625rem; }
                .p-3 { padding: 0.75rem; }
                .px-1 { padding-left: 0.25rem; padding-right: 0.25rem; }
                .px-1\\.5 { padding-left: 0.375rem; padding-right: 0.375rem; }
                .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
                .px-2\\.5 { padding-left: 0.625rem; padding-right: 0.625rem; }
                .px-4 { padding-left: 1rem; padding-right: 1rem; }
                .py-0\\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
                .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
                .py-1\\.5 { padding-top: 0.375rem; padding-bottom: 0.375rem; }
                .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
                .mb-1 { margin-bottom: 0.25rem; }
                .mb-2 { margin-bottom: 0.5rem; }
                .mb-3 { margin-bottom: 0.75rem; }
                .mb-4 { margin-bottom: 1rem; }
                .mb-6 { margin-bottom: 1.5rem; }
                .mb-16 { margin-bottom: 4rem; }
                .mt-0\\.5 { margin-top: 0.125rem; }
                .mt-1 { margin-top: 0.25rem; }
                .mt-2 { margin-top: 0.5rem; }
                .mt-3 { margin-top: 0.75rem; }
                .mt-4 { margin-top: 1rem; }
                .mt-6 { margin-top: 1.5rem; }
                .mt-8 { margin-top: 2rem; }
                .pt-2 { padding-top: 0.5rem; }
                .pt-3 { padding-top: 0.75rem; }
                .pt-4 { padding-top: 1rem; }
                .pb-0\\.5 { padding-bottom: 0.125rem; }
                .pb-1\\.5 { padding-bottom: 0.375rem; }
                .pb-2 { padding-bottom: 0.5rem; }
                .pb-3 { padding-bottom: 0.75rem; }
                .pb-4 { padding-bottom: 1rem; }
                .pl-6 { padding-left: 1.5rem; }
                .pl-8 { padding-left: 2rem; }
                .mr-1 { margin-right: 0.25rem; }
                .mr-1\\.5 { margin-right: 0.375rem; }
                .mr-2 { margin-right: 0.5rem; }
                .ml-1 { margin-left: 0.25rem; }
                .min-w-\\[180px\\] { min-width: 180px; }
                .inline-block { display: inline-block; }
                .block { display: block; }
                .w-16 { width: 4rem; }
                .h-16 { height: 4rem; }
                .w-full { width: 100%; }
                .h-full { height: 100%; }
                .object-contain { object-fit: contain; }
                .shrink-0 { flex-shrink: 0; }
                .italic { font-style: italic; }
                .underline { text-decoration: underline; }
                .break-inside-avoid { break-inside: avoid; }
            </style>
        </head>
        <body>
            ${c.value.innerHTML}
        </body>
        </html>
    `),t.close(),setTimeout(()=>{e.contentWindow.focus(),e.contentWindow.print(),setTimeout(()=>{document.body.contains(e)&&document.body.removeChild(e)},1500)},300)};return(t,r)=>n.show?(e(),p(`div`,Q,[s(`div`,$,[s(`div`,ee,[s(`div`,te,[r[3]||=s(`div`,{class:`w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs`},` A4 `,-1),s(`div`,null,[s(`h3`,ne,m(n.title),1),r[2]||=s(`p`,{class:`text-[10px] text-slate-400`},`Simulasi Tampilan Kertas Cetak Standar Akuntansi Mode Terang`,-1)])]),s(`div`,re,[s(`button`,{type:`button`,onClick:l,class:`px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-bold rounded-xl transition-all flex items-center space-x-2 shadow-md cursor-pointer`},[...r[4]||=[s(`svg`,{class:`w-4 h-4`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[s(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z`})],-1),s(`span`,null,`Cetak / Simpan PDF`,-1)]]),s(`button`,{type:`button`,onClick:r[0]||=e=>o(`close`),class:`p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer`,title:`Tutup Pratinjau`},[...r[5]||=[s(`svg`,{class:`w-5 h-5`,fill:`none`,stroke:`currentColor`,viewBox:`0 0 24 24`},[s(`path`,{"stroke-linecap":`round`,"stroke-linejoin":`round`,"stroke-width":`2`,d:`M6 18L18 6M6 6l12 12`})],-1)]])])]),s(`div`,ie,[s(`div`,{ref_key:`printSheetRef`,ref:c,class:`w-full max-w-[210mm] bg-white text-slate-900 shadow-2xl p-8 sm:p-12 border border-slate-300 rounded-sm min-h-[297mm] print:shadow-none print:border-none print:p-0 print:m-0 print:min-h-0 print:max-w-full print:w-full print-sheet`},[i(t.$slots,`default`)],512)]),s(`div`,ae,[r[6]||=s(`span`,null,[f(`💡 Tip: Gunakan orientasi `),s(`strong`,null,`Portrait`),f(` dan centang `),s(`strong`,null,`Background Graphics`),f(` saat mencetak via browser.`)],-1),s(`button`,{type:`button`,onClick:r[1]||=e=>o(`close`),class:`px-4 py-1.5 bg-slate-200 hover:bg-slate-300 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-gray-300 font-semibold rounded-lg transition-colors cursor-pointer`},` Tutup `)])])])):u(``,!0)}};export{Z as n,j as r,oe as t};