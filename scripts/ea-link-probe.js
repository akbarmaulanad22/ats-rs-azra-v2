/*
 * ea-link-probe.js  --  Sparx EA 15.2, JScript (WSH/ES3). READ-ONLY.
 *
 * GOAL: learn how EA stores a MANUAL connector bend (custom waypoints), so the
 * generator can write explicit orthogonal routes with spacing.
 *
 * STEPS  (the SAVE step is essential -- waypoints only persist once saved)
 *   1. Open any generated activity diagram (e.g. the UC-16 one).
 *   2. Pick ONE connector that overlaps others. Click it, drag its middle to
 *      make a clear right-angle bend (drag twice for a Z shape). Remember its
 *      source -> target. Leave the other connectors untouched for contrast.
 *   3. SAVE the diagram: Ctrl+S (or click Save). THIS WRITES THE GEOMETRY.
 *   4. Project Browser: single-click the PACKAGE that holds the diagram.
 *   5. Scripting > paste this > Run. Copy ALL output back.
 *
 * The bent connector's Path / Geometry will differ from the untouched ones --
 * that delta is the waypoint format I need.
 */

function out(s) { Session.Output(s); }
function safe(fn) {
    try { var v = fn(); if (v === null || typeof v === "undefined") { return "<null>"; } return "" + v; }
    catch (e) { return "<err:" + e.description + ">"; }
}
function line() { out("------------------------------------------------------------"); }

function resolveDiagram(repo) {
    var d = null;
    try { d = repo.GetCurrentDiagram(); } catch (e1) { d = null; }
    if (d !== null) { return d; }
    var sel = repo.GetTreeSelectedObject();
    if (sel === null) { return null; }
    if (sel.ObjectType === 8) { return sel; }
    if (sel.ObjectType === 5 && sel.Diagrams.Count > 0) { return sel.Diagrams.GetAt(0); }
    return null;
}

function main() {
    var repo = Repository;
    var dia = resolveDiagram(repo);
    if (dia === null) {
        out("No diagram. Open it or single-click its package, then re-run.");
        return;
    }
    // force a fresh DB read so saved geometry is reflected
    try { repo.ReloadDiagram(dia.DiagramID); } catch (eR) {}
    line();
    out("LINK PROBE  --  diagram: " + safe(function () { return dia.Name; }));
    out("Look for the connector whose Path/Geometry is RICHER than the rest:");
    out("that is the one you hand-bent; its format is the waypoint format.");
    line();

    var links = dia.DiagramLinks;
    var k;
    for (k = 0; k < links.Count; k++) {
        var dl = links.GetAt(k);
        var con = repo.GetConnectorByID(dl.ConnectorID);
        var srcId = safe(function () { return con.ClientID; });
        var tgtId = safe(function () { return con.SupplierID; });
        var srcNm = "?", tgtNm = "?";
        try { srcNm = repo.GetElementByID(con.ClientID).Name; } catch (e2) {}
        try { tgtNm = repo.GetElementByID(con.SupplierID).Name; } catch (e3) {}
        out("[" + k + "] ConnID=" + dl.ConnectorID +
            "  " + srcNm + " (" + srcId + ") -> " + tgtNm + " (" + tgtId + ")");
        out("     Style    = " + safe(function () { return dl.Style; }));
        out("     Geometry = " + safe(function () { return dl.Geometry; }));
        out("     Path     = " + safe(function () { return dl.Path; }));
        out("     ----");
    }
    line();
    out("LINK PROBE DONE. Copy everything. Tell me which source->target you bent.");
}

main();
