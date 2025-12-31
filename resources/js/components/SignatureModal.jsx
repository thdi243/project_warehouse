import SignatureCanvas from "react-signature-canvas";
import { Button } from "@/components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { useRef } from "react";

export default function SignatureModal({ open, onClose, onSave }) {
    const sigRef = useRef(null);

    const handleSave = () => {
        if (sigRef.current.isEmpty()) {
            alert("TTD belum diisi");
            return;
        }

        const dataUrl = sigRef.current.getCanvas().toDataURL("image/png");

        onSave(dataUrl);
        onClose();
    };

    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Tanda Tangan</DialogTitle>
                </DialogHeader>

                <div className="border rounded">
                    <SignatureCanvas
                        ref={sigRef}
                        penColor="black"
                        canvasProps={{
                            width: 450,
                            height: 200,
                            className: "bg-white",
                        }}
                    />
                </div>

                <div className="flex justify-between mt-4">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => sigRef.current.clear()}
                    >
                        Clear
                    </Button>

                    <Button type="button" onClick={handleSave}>
                        Simpan & Submit
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
