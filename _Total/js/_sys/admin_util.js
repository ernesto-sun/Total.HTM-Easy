
// ----------------------------------------------------------------
function NAV_LI_make(n, sym, f)
{
    var dli = fCE("li"),
        dd = dli.CE("div").Ca("li-line"),
        da = dd.CE("a").Ca("done-a"),
        href = "#",
        y = typeof f;

    da.CE("span").APs(n);

    if (y == "string")
    {
        href = f;
        f = aclick;
    }
    else if(y == UN) 
    {
        // make what DYN-DOM would do. 
        f = aclick;
    }
    
    da.Ea("click", f).A("href", href);

    if(typeof sym == "string" && sym.length > 0)
    {        
        da.AP0(SYM_make(sym, 1));
    }
    return dli;
}
