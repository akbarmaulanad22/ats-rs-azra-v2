/*
 * ea-activity-probe.js  --  Sparx Enterprise Architect 15.2, JScript (WSH/ES3)
 *
 * PURPOSE
 *   Read ground-truth automation constants off a diagram YOU draw by hand,
 *   so the generator is built on real values, not recalled guesses.
 *
 * HOW TO RUN
 *   1. In EA: create a new Activity diagram (any package).
 *   2. From the Activity toolbox, drop ONE of each onto it:
 *        - Initial            (filled dot)
 *        - Action             (rounded box)   -> name it "Aksi"
 *        - Decision           (diamond)
 *        - Activity Final     (bullseye)
 *        - Flow Final         (X in circle)
 *      Then add TWO VERTICAL swimlanes (Diagram > Swimlanes & Matrix, or the
 *      toolbox), title them "Aktor" and "Sistem".
 *      Draw 2-3 Control Flow connectors between the nodes, including one that
 *      crosses from the Aktor lane into the Sistem lane.
 *   3. In the PROJECT BROWSER tree, single-click the diagram node so it is
 *      highlighted (this is the reliable selection the probe reads). Leaving
 *      the diagram open as the active tab also works as a fallback.
 *   4. EA menu: Specialize > Tools > Scripting. New JScript group + script,
 *      paste this whole file, press Run (green arrow).
 *   5. Output appears in the Scripting output tab. Copy ALL of it back to me.
 *
 * It only READS. It changes nothing.
 */

var EOL = "\n";

function out(s) {
    // Session is in scope inside EA's scripting window.
    Session.Output(s);
}

function safe(fn) {
    // returns the value of fn() or "<err:...>" without aborting the probe
    try {
        var v = fn();
        if (v === null || typeof v === "undefined") {
            return "<null>";
        }
        return "" + v;
    } catch (e) {
        return "<err:" + e.description + ">";
    }
}

function line() {
    out("------------------------------------------------------------");
}

function main() {
    var repo = Repository;
    line();
    out("EA ACTIVITY PROBE");
    out("EA library version : " + safe(function () { return repo.LibraryVersion; }));
    line();

    // Resolve the target diagram. GetCurrentDiagram() often returns null when
    // the scripting window has focus, so fall back to the Project Browser
    // tree selection (otDiagram == 8).
    var dia = null;
    try {
        dia = repo.GetCurrentDiagram();
    } catch (eCur) {
        dia = null;
    }
    if (dia === null) {
        out("GetCurrentDiagram() was null -- trying Project Browser selection.");
        try {
            var sel = repo.GetTreeSelectedObject();
            if (sel === null) {
                out("  Tree selection is null.");
            } else {
                var ot = sel.ObjectType; // 8=diagram, 5=package, 4=element
                out("  Tree selection ObjectType = " + ot);
                if (ot === 8) {
                    dia = sel;
                } else if (ot === 5) {
                    // package -> use its first diagram
                    var pkgDias = sel.Diagrams;
                    out("  Selected package has " + pkgDias.Count + " diagram(s).");
                    if (pkgDias.Count > 0) {
                        dia = pkgDias.GetAt(0);
                        out("  Using first diagram in package: " + dia.Name);
                    }
                } else if (ot === 4) {
                    // element -> use first diagram owned by it (composite)
                    var elDias = sel.Diagrams;
                    if (elDias.Count > 0) {
                        dia = elDias.GetAt(0);
                    }
                }
            }
        } catch (eSel) {
            out("  GetTreeSelectedObject failed: " + eSel.description);
        }
    }
    if (dia === null) {
        out("NO DIAGRAM RESOLVED. Easiest fix: DOUBLE-CLICK the diagram in the");
        out("Project Browser to open it as a tab, then re-run. Or single-click");
        out("the PACKAGE that contains it and re-run.");
        return;
    }

    out("DIAGRAM");
    out("  Name      : " + safe(function () { return dia.Name; }));
    out("  Type      : " + safe(function () { return dia.Type; }));
    out("  MetaType  : " + safe(function () { return dia.MetaType; }));
    out("  DiagramID : " + safe(function () { return dia.DiagramID; }));
    line();

    // ---- SWIMLANES ---------------------------------------------------------
    out("SWIMLANEDEF");
    var sld = null;
    try {
        sld = dia.SwimlaneDef;
    } catch (e) {
        out("  <no SwimlaneDef: " + e.description + ">");
    }
    if (sld !== null) {
        out("  Orientation : " + safe(function () { return sld.Orientation; }));
        out("  Bold        : " + safe(function () { return sld.Bold; }));
        out("  HideNames   : " + safe(function () { return sld.HideNames; }));
        out("  ShowInBar   : " + safe(function () { return sld.ShowInBar; }));
        var lanes = null;
        try {
            lanes = sld.Swimlanes;
        } catch (e2) {
            out("  <no Swimlanes coll: " + e2.description + ">");
        }
        if (lanes !== null) {
            out("  Swimlanes.Count = " + safe(function () { return lanes.Count; }));
            var i;
            for (i = 0; i < lanes.Count; i++) {
                var ln = lanes.GetAt(i);
                out("    [" + i + "] Title=" + safe(function () { return ln.Title; }) +
                    "  Width=" + safe(function () { return ln.Width; }) +
                    "  ClassifierGUID=" + safe(function () { return ln.ClassifierGUID; }));
            }
        }
    }
    line();

    // ---- DIAGRAM OBJECTS (nodes + geometry) --------------------------------
    out("DIAGRAM OBJECTS (node geometry + element type/subtype)");
    out("  NOTE: in EA, top/bottom are stored NEGATIVE (y grows downward).");
    var objs = dia.DiagramObjects;
    out("  DiagramObjects.Count = " + objs.Count);
    var j;
    for (j = 0; j < objs.Count; j++) {
        var dobj = objs.GetAt(j);
        var eid = dobj.ElementID;
        var el = repo.GetElementByID(eid);
        out("  ----");
        out("  [" + j + "] ElementID=" + eid);
        out("       Name      = " + safe(function () { return el.Name; }));
        out("       Type      = " + safe(function () { return el.Type; }));
        out("       Subtype   = " + safe(function () { return el.Subtype; }));
        out("       MetaType  = " + safe(function () { return el.MetaType; }));
        out("       Stereotype= " + safe(function () { return el.Stereotype; }));
        out("       geom L/R/T/B = " +
            safe(function () { return dobj.left; }) + " / " +
            safe(function () { return dobj.right; }) + " / " +
            safe(function () { return dobj.top; }) + " / " +
            safe(function () { return dobj.bottom; }));
    }
    line();

    // ---- CONNECTORS (type + routing geometry) ------------------------------
    out("DIAGRAM LINKS (connector type + saved routing geometry)");
    var links = dia.DiagramLinks;
    out("  DiagramLinks.Count = " + links.Count);
    var k;
    for (k = 0; k < links.Count; k++) {
        var dl = links.GetAt(k);
        var cid = dl.ConnectorID;
        var con = repo.GetConnectorByID(cid);
        out("  ----");
        out("  [" + k + "] ConnectorID=" + cid);
        out("       Type      = " + safe(function () { return con.Type; }));
        out("       MetaType  = " + safe(function () { return con.MetaType; }));
        out("       Stereotype= " + safe(function () { return con.Stereotype; }));
        out("       LineStyle = " + safe(function () { return dl.LineStyle; }));
        out("       Geometry  = " + safe(function () { return dl.Geometry; }));
        out("       Path      = " + safe(function () { return dl.Path; }));
    }
    line();
    out("PROBE DONE. Copy everything above back.");
}

main();
