import { Button } from '@/components/ui/button';

type Props = { archived: boolean; onChange: (value: boolean) => void; activeCount: number; archivedCount: number };

export default function HonorariumRecordFilter({ archived, onChange, activeCount, archivedCount }: Props) {
    return (
        <div className="space-y-2">
            <div className="flex flex-wrap gap-2" role="group" aria-label="Defense record category">
                <Button variant={!archived ? 'default' : 'outline'} aria-pressed={!archived} onClick={() => onChange(false)}>
                    Active ({activeCount})
                </Button>
                <Button variant={archived ? 'default' : 'outline'} aria-pressed={archived} onClick={() => onChange(true)}>
                    Rejected / Archived ({archivedCount})
                </Button>
            </div>
            {archived && (
                <p className="text-muted-foreground text-sm">
                    Rejected and cancelled defenses are kept for reference. Receipts and history remain available; further honorarium processing is
                    unavailable.
                </p>
            )}
        </div>
    );
}
