
// ----------------------------------------------------------------
function NAV_LI_make(n, sym, f)
{
    var dli = fCE("li"),
        dd = dli.CE("div"),
        da = dd.CE("a").Ca("done-a"),
        ds = da.CE("span").APs(n),
        href = "#",
        y = typeof f;

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
