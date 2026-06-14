/*
 * ea-seq-probe.js  --  Sparx Enterprise Architect 15.2, JScript (WSH/ES3)
 *
 * PURPOSE
 *   Read ground-truth automation constants off a SEQUENCE diagram YOU draw by
 *   hand, so the generator (ea-sequence-build.js) is built on real measured
 *   values, not recalled guesses. Sequence internals (lifeline element type,
 *   message connector type, message ORDER storage, and especially the
 *   COMBINED FRAGMENT element + its alt/opt operator and operands) differ
 *   completely from Activity and are the parts most likely to render "messy"
 *   if guessed. This probe measures all of them.
 *
 * WHAT TO DRAW (small but must contain every shape the engine emits)
 *   1. In EA: create a new *Sequence* diagram (any package).
 *   2. From the Sequence toolbox, drop THREE lifelines left-to-right and name
 *      them so they're easy to spot:
 *        - "Aktor"        (an Actor lifeline if the toolbox offers it)
 *        - "Controller"   (an Object/lifeline)
 *        - "Model"        (an Object/lifeline)
 *   3. Draw messages between them, TOP TO BOTTOM in this order:
 *        m1: Aktor      -> Controller   sync call,  name "store()"
 *        m2: Controller -> Model        sync call,  name "save()"
 *        m3: Model      --> Controller  RETURN msg,  name "ok"   (dashed)
 *      (The vertical order matters -- we measure how EA stores it.)
 *   4. Drop ONE Combined Fragment over the Controller/Model messages and set
 *      its operator to "alt". Give it TWO operands (two regions) with guards
 *      "[lulus]" and "[gagal]". Put m2/m3 inside the first operand.
 *      (Combined Fragment lives in the "Interaction Relationships"/fragment
 *      part of the Sequence toolbox, or right-click diagram > New Element.)
 *   5. REQUIRED: ensure an activation / focus-of-control bar shows on the
 *      Controller lifeline under m1 (sync calls usually auto-render one; if
 *      not, right-click the message > enable activation). Activation bars are
 *      ON in the locked design, so the probe must capture how EA stores them
 *      -- either as auto-rendered focus (engine creates nothing) or as a
 *      distinct element/flag we must set.
 *
 * HOW TO RUN
 *   - Single-click the diagram node in the PROJECT BROWSER (reliable
 *     selection), or leave it open as the active tab.
 *   - EA menu: Specialize > Tools > Scripting. New JScript group + script,
 *     paste this whole file, press Run (green arrow).
 *   - Copy ALL of the Scripting output tab back to me.
 *
 * It only READS. It changes nothing.
 */

var EOL = "\n";

function out(s) {
    Session.Output(s);
}

function safe(fn) {
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

// dump TaggedValues of an element or connector (combined-fragment operator,
// message kind, and sequence metadata often hide here)
function dumpTaggedValues(label, owner) {
    var tv = null;
    try {
        tv = owner.TaggedValues;
    } catch (e) {
        out("    " + label + " TaggedValues: <err:" + e.description + ">");
        return;
    }
    if (tv === null) {
        out("    " + label + " TaggedValues: <null>");
        return;
    }
    out("    " + label + " TaggedValues.Count = " + safe(function () { return tv.Count; }));
    var i;
    for (i = 0; i < tv.Count; i++) {
        var t = tv.GetAt(i);
        out("      <" + safe(function () { return t.Name; }) + "> = " +
            safe(function () { return t.Value; }));
    }
}

// dump MiscData[0..4]; EA stowes combined-fragment operator/subtype and some
// connector flags in these legacy slots
function dumpMiscData(label, owner) {
    var i;
    for (i = 0; i < 5; i++) {
        out("    " + label + " MiscData[" + i + "] = " +
            safe((function (idx) { return function () { return owner.MiscData(idx); }; })(i)));
    }
}

function main() {
    var repo = Repository;
    line();
    out("EA SEQUENCE PROBE");
    out("EA library version : " + safe(function () { return repo.LibraryVersion; }));
    line();

    // Resolve target diagram (same fallback chain as the activity probe).
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
                    var pkgDias = sel.Diagrams;
                    out("  Selected package has " + pkgDias.Count + " diagram(s).");
                    if (pkgDias.Count > 0) {
                        dia = pkgDias.GetAt(0);
                        out("  Using first diagram in package: " + dia.Name);
                    }
                } else if (ot === 4) {
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
        out("NO DIAGRAM RESOLVED. DOUBLE-CLICK the sequence diagram in the");
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

    // ---- DIAGRAM OBJECTS (lifelines + combined fragment + geometry) --------
    out("DIAGRAM OBJECTS (lifeline / fragment geometry + element type/subtype)");
    out("  NOTE: in EA, top/bottom are stored NEGATIVE (y grows downward).");
    out("  Lifelines: note Type/MetaType. Combined Fragment: note Type/MetaType,");
    out("  Subtype, and where its operator (alt/opt) + operand guards live.");
    var objs = dia.DiagramObjects;
    out("  DiagramObjects.Count = " + objs.Count);
    var j;
    for (j = 0; j < objs.Count; j++) {
        var dobj = objs.GetAt(j);
        var eid = dobj.ElementID;
        var el = repo.GetElementByID(eid);
        out("  ----");
        out("  [" + j + "] ElementID=" + eid +
            "  Sequence(DiagObj)=" + safe(function () { return dobj.Sequence; }));
        out("       Name      = " + safe(function () { return el.Name; }));
        out("       Type      = " + safe(function () { return el.Type; }));
        out("       Subtype   = " + safe(function () { return el.Subtype; }));
        out("       MetaType  = " + safe(function () { return el.MetaType; }));
        out("       Stereotype= " + safe(function () { return el.Stereotype; }));
        out("       ParentID  = " + safe(function () { return el.ParentID; }));
        out("       geom L/R/T/B = " +
            safe(function () { return dobj.left; }) + " / " +
            safe(function () { return dobj.right; }) + " / " +
            safe(function () { return dobj.top; }) + " / " +
            safe(function () { return dobj.bottom; }));
        dumpMiscData("element", el);
        dumpTaggedValues("element", el);
        // operands of a combined fragment are usually child elements
        var kids = null;
        try {
            kids = el.Elements;
        } catch (eK) {
            kids = null;
        }
        if (kids !== null && kids.Count > 0) {
            out("       child Elements.Count = " + kids.Count + " (operands?)");
            var ck;
            for (ck = 0; ck < kids.Count; ck++) {
                var kid = kids.GetAt(ck);
                out("         kid[" + ck + "] Name=" + safe(function () { return kid.Name; }) +
                    "  Type=" + safe(function () { return kid.Type; }) +
                    "  MetaType=" + safe(function () { return kid.MetaType; }) +
                    "  Subtype=" + safe(function () { return kid.Subtype; }));
                dumpTaggedValues("       kid", kid);
            }
        }
    }
    line();

    // ---- CONNECTORS (message type + ORDER storage + sync/return) -----------
    out("DIAGRAM LINKS (message connector type + ordering + sync/return flags)");
    out("  Need to learn: message Type/MetaType, how ORDER is stored");
    out("  (SequenceNo on connector and/or DiagramLink), sync-call vs return,");
    out("  and where the message name/arguments live.");
    var links = dia.DiagramLinks;
    out("  DiagramLinks.Count = " + links.Count);
    var k;
    for (k = 0; k < links.Count; k++) {
        var dl = links.GetAt(k);
        var cid = dl.ConnectorID;
        var con = repo.GetConnectorByID(cid);
        out("  ----");
        out("  [" + k + "] ConnectorID=" + cid);
        out("       Name        = " + safe(function () { return con.Name; }));
        out("       Type        = " + safe(function () { return con.Type; }));
        out("       MetaType    = " + safe(function () { return con.MetaType; }));
        out("       Subtype     = " + safe(function () { return con.Subtype; }));
        out("       Stereotype  = " + safe(function () { return con.Stereotype; }));
        out("       Direction   = " + safe(function () { return con.Direction; }));
        out("       ClientID    = " + safe(function () { return con.ClientID; }));
        out("       SupplierID  = " + safe(function () { return con.SupplierID; }));
        out("       SequenceNo  = " + safe(function () { return con.SequenceNo; }));
        out("       TransitionGuard = " + safe(function () { return con.TransitionGuard; }));
        out("       DiagramLink.SeqNo = " + safe(function () { return dl.SeqNo; }));
        out("       LineStyle   = " + safe(function () { return dl.LineStyle; }));
        out("       Geometry    = " + safe(function () { return dl.Geometry; }));
        out("       Path        = " + safe(function () { return dl.Path; }));
        dumpMiscData("conn", con);
        dumpTaggedValues("conn", con);
        // message arguments / properties sometimes carry sync/async + return
        var cp = null;
        try {
            cp = con.Properties;
        } catch (eP) {
            cp = null;
        }
        if (cp !== null) {
            out("       Connector.Properties: Effect=" + safe(function () { return cp.Effect; }) +
                "  Kind=" + safe(function () { return cp.Kind; }) +
                "  Synch=" + safe(function () { return cp.Synch; }));
        }
    }
    line();
    out("PROBE DONE. Copy everything above back.");
}

main();
