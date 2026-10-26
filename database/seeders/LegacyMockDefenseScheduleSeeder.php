<?php

namespace Database\Seeders;

use App\Models\DefenseRequest;
use App\Models\Panelist;
use App\Models\ProgramRecord;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LegacyMockDefenseScheduleSeeder extends Seeder
{
    private const PROGRAMS = [
        'MAEM' => ['Master of Arts in Educational Management', 'MAEM'],
        'MAEDPE' => ['Master of Arts in Education, Major in Physical Education', 'MAED-PE'],
        'MAED-PE' => ['Master of Arts in Education, Major in Physical Education', 'MAED-PE'],
        'MAED-GC' => ['Master of Arts in Education, Major in Guidance and Counseling', 'MAED-GC'],
        'MAEED' => ['Master of Arts in Elementary Education', 'MAEE'],
        'MAEDSOCIO' => ['Master of Arts in Education, Major in Sociology', 'MAED-SOC'],
        'MAED-SOCIO' => ['Master of Arts in Education, Major in Sociology', 'MAED-SOC'],
        'MAEDMATH' => ['Master of Arts in Education, Major in Mathematics', 'MAED-MATH'],
        'MAED-MATH' => ['Master of Arts in Education, Major in Mathematics', 'MAED-MATH'],
        'MAED-ITI' => ['Master of Arts in Education, Major in Information Technology Integration', 'MAED-ITI'],
        'MAVE' => ['Master of Arts in Values Education', 'MAVE'],
        'MARE' => ['Master of Arts in Religious Education', 'MARE'],
    ];

    public function run(): void
    {
        $rosters = [
            ['Dr. Virgion Mamonong', [
                'BANGCAS, JOANNAH ROSE C.|MAEM',
                'LUZON, RITCHEL O.|MAEED',
                'NUMERON, ELIZA G.|MAEM',
                'PANALANGIN, HARON A.|MAEDPE',
                'PARDILLO, MA. ERIKA VICTORIA R.|MAEDPE',
                'ROJO, JANE H.|MAEED',
            ]],
            ['Dr. Nida Matutino', ['BERNAS, NERISSA C.|MAEDPE']],
            ['Dr. Mona L. Laya', ['CANDARI, HARELD O.|MAEM']],
            ['Dr. Benedicta Lascano', ['DOMINGUEZ, DONNA G.|MAED-GC']],
            ['Dr. Thelma Alderite', [
                'MACATABOG, LOIDEL U.|MAEED',
                'ALBARANDO, CHARITY L.|MAEM',
                'ALIGATO, MARY ANN F.|MAEM',
                'DIGANDANG, AMINA B.|MAEM',
                'DUMAPIAS, O.P., SR. JOSEFA E.|MAEM',
                'GABAN, ELYZZA CARMELLE F.|MAEM',
                'LATRAS, JOHANNES T.|MAEM',
                'MORAN, RVM, S. MA. MAY N.|MAEM',
                'OPADA, JECY B.|MAEM',
                'ROYO, MELODY I.|MAEM',
                'SALDUA, MIC, SR. GERARLYN V.|MAEM',
                'ATIS, IRENE C.|MAVE',
                'BAGONGON , MARY QUEEN Z.|MAVE',
                'BALGOS, LIONELLE REI C.|MAEED',
                'COLOMA, MARYLOU J.|MAVE',
                'DALISAY, TON VINCENT S.|MAED-SOCIO',
                'ELARMO, SHIELA ROSE A.|MAVE',
                'NAPIGKIT, BRENT RYAN M.|MAVE',
                'ARMADA, RYSON P.|MAED-ITI',
                'EJANDRA, JEANETTE L.|MAED-SOCIO',
                'GUITGUIT, ARNIE BOY O.|MAED-MATH',
                'JAMERA, ANGELMAE E.|MAEED',
                'LAMALAN, STEFFI U.|MAEED',
                'MATILAC, MICHELLE E.|MAEED',
                'MUSTRADA, LEONIE D.|MAEED',
                'OTADOY, JANNETH A.|MAED-MATH',
                'PABELLON, GRACE JOY Q.|MAEED',
                'RAVELO, DESIREE L.|MAED-SOCIO',
                'REFULLE, BUTCHICK D.|MAEED',
                'SAMPIANO, RICHELLE L.|MAVE',
                'TORRES, MARIANNE C.|MAVE',
                'VILLANUEVA, JANE D.|MAED-MATH',
                'ZABALA, KIMJEWARD E.|MAED-MATH',
            ]],
            ['Dr. Jo-Ann Y. Solomon', [
                'SABPA, JOHARA M.|MAEDPE',
                'CLAPANO, JEZREEL HOPE MARIEZL M.|MAEDPE',
            ]],
            ['Mr. Rene M. Babiera', ['SALVAÑA, REYMOND B.|MAEDPE']],
            ['Dr. Sylvia Pidor', [
                'AGRAVIADOR, RAFAEL BRIAN D.|MAEED',
                'ANDRES, CAREL FAITH M.|MAEDSOCIO',
                'BARATAS, DOLORES M.|MAEDMATH',
                'BRIGOLE, JENESA A.|MAEDMATH',
                'CALZADA, RODERICK B.|MAEED',
                'CORDERO, ANALYN I.|MAEDPE',
                'LEGURPA, ROSENDO V.|MAEDPE',
                'MALCE, RIZALY J.|MAEDSOCIO',
                'MAPUTOL, GINALYN A.|MAEDMATH',
                'MENSIS, ALENJUN B.|MAEDMATH',
                'NARIT, MARIA METHUSELA AMOR F.|MAEM',
                'PLANAS, KRISTINE CLARRISSE C.|MAEDMATH',
                'TAMAYO, RUBY B.|MAEM',
                'AKMAD, ARIFF YSMAEL K.|MAED-PE',
                'ALMERO, EDDCYN P.|MAEM',
                'CALIG-ONAN, INDIRA ZYRA M.|MAEED',
                'CAMPOSO, RODEL C.|MAEM',
                'CUADRADO, KENT PETER S.|MAED-PE',
                'DAPITAN, DEXTER C.|MAED-PE',
                'FABRERO, KRYSS MAYVEN L.|MAED-PE',
                'FERNANDEZ, EARL RUSSEL JOHN C.|MAED-PE',
                'GARDE, SR. ROSELYN R.|MARE',
                'HADJIROL, JASMIN O.|MAVE',
                'HAPIE, JUL ALGAFFAR V.|MAED-PE',
                'LARGO, JARIEV R.|MAEM',
                'MAGTULIS, MICHELLE E.|MARE',
                'MANGUBAT, CHRISTINE MAY T.|MAED-PE',
                'NONLES, RICO E.|MAEM',
                'SUMOGOD, VALERIE L.|MAEM',
            ]],
            ['Dr. Ma. Eva San Juan', ['AKIATAN, JOHN RIV E.|MAEDPE']],
            ['Dr. Nora J. Albores', ['BONJOC, ARIEL N.|MAEDPE']],
            ['Ronell Cervera', ['MAKADATO, NORODIN G.|MAEDPE']],
            ['Gelsa Dragon', ['MAMBURAM, MARWIN GEL B.|MAEDPE']],
            ['Dr. Edna Salva', [
                'PARAMO, RUBEN S.|MAEDMATH',
                'ALCULAR, GLORY MAY M.|MAVE',
                'AMAD, CECIL L.|MAVE',
                'ANCHETA, HELENE E.|MAEED',
                'BEJOD, FLORIFES C.|MAEM',
                'DELIMA, GERALDEN C.|MAEED',
                'MARQUEZ, MARIEL JAM A.|MAED-PE',
                'MATAIS, MARLON M.|MAEM',
                'OPONG, PRINCESS D.|MAED-PE',
                'PARBA, DEE JHAY M.|MAEM',
                'PELONITA, REY JHONSON L.|MAVE',
                'PICCIO, KRIS CARLO P.|MAEM',
                'RABAÑO, AIZA A.|MAED-PE',
                'SABELLANO, JENNIFER B.|MAED-MATH',
                'SULIT, JEFFREY J.|MAED-PE',
                'TABLON, EVE B.|MAEM',
                'VILLAR, KARLA ALYANNA C.|MAEM',
            ]],
            ['Rene M. Babiera', ['TUAN, JAYSON P.|MAEDPE']],
            ['Dr. Lolly Jean Simbulas', [
                'ALCEBAR, IRISH N.|MAED-MATH',
                'ALI, DEXTER B.|MAED-MATH',
                'ALVARADO, MARECRIS T.|MAED-MATH',
                'COLMAN, SHAWN C.J. A.|MAED-MATH',
                'DESCUATAN, GLENN T.|MAED-MATH',
                'GENEROSA, ARMADET A.|MAED-ITI',
                'GUARDANOSA, SENEN IAN N.|MAED-MATH',
                'LABASANO, SHABBY I.|MAED-MATH',
                'LOPEZ, ELLA MAE M.|MAED-MATH',
                'MULAWAN, SHEILA L.|MAED-MATH',
                'PATE, MA. AMOR M.|MAED-MATH',
                'QUINTANO, RONNAN PATRICK JOHN C.|MAED-MATH',
                'SONGCAT, ROVILYN JANE G.|MAED-MATH',
            ]],
            ['Dr. Erick Baloran', [
                'ABAD, MISHEL G.|MAED-PE',
                'ALABAT, MARIA JOEMELA I.|MAED-PE',
                'BISCAYNO, LESTER B.|MAED-PE',
                'CAPILITAN, GLENDMORE D.|MAED-PE',
                'FALCIS, JESSICA B.|MAED-PE',
                'LUGPIT, JESSIFEL B.|MAED-PE',
                'MELGO, JAMES B.|MAED-PE',
                'MELGO, LEANNE LUCILLE A.|MAED-PE',
                'NOCOS, JANETH L.|MAED-PE',
                'OPISO, KARA ANGELA E.|MAED-PE',
                'ORTIZO, APRIL CYNTHS T.|MAED-PE',
                'PACRES, KLYDE RAFFY M.|MAED-PE',
                'PACRES, KRISTINE JOY P.|MAED-PE',
                'TALIB, RHODA .|MAED-PE',
            ]],
            ['Dr. Porferia S. Poralan', [
                'BAAN, BENNY M.|MAED-PE',
                'BACOG, FRILE ANN R.|MAED-MATH',
                'BARRIOS, MARIA KATE A.|MAED-PE',
                'BERNABE, AIRISH E.|MAEM',
                'CANDA, JOY ANGELICA C.|MAEED',
                'CELESTIAL, STEPHEN D.|MAED-PE',
                'GAPOY, EDWARD LOUIS C.|MAED-MATH',
                'JUMAWAN, JONA JANE F.|MAEM',
                'MATA, JEGGER P.|MAED-PE',
                'MELEJOR, ERENIO JR. L.|MAED-SOCIO',
                'SALUPAN, NESIE B.|MAEM',
            ]],
        ];
        $studentCount = array_sum(array_map(fn (array $roster) => count($roster[1]), $rosters));

        DB::transaction(function () use ($rosters): void {
            $students = [];
            foreach ($rosters as [$adviser, $entries]) {
                foreach ($entries as $entry) {
                    [$name, $programCode] = explode('|', $entry, 2);
                    $students[] = [
                        'name' => trim($name),
                        'program_code' => Str::upper(trim($programCode)),
                        'adviser' => $adviser,
                    ];
                }
            }

            $this->ensureProgramsExist($students);
            [$chairs, $members] = $this->ensureMockPanelists();
            $slots = [
                ['08:00:00', '09:00:00'],
                ['09:00:00', '10:00:00'],
                ['10:00:00', '11:00:00'],
                ['13:00:00', '14:00:00'],
                ['14:00:00', '15:00:00'],
                ['15:00:00', '16:00:00'],
            ];
            $firstDefenseDate = $this->nextWeekday();
            $year = now()->year;

            foreach ($students as $index => $student) {
                $slot = $slots[$index % count($slots)];
                $date = $firstDefenseDate->copy()->addWeekdays(intdiv($index, count($slots)));
                $scheduledAt = $date->copy()->setTimeFromTimeString($slot[0]);
                $scheduledEnd = $date->copy()->setTimeFromTimeString($slot[1]);
                $panelistOffset = $index % count($members);
                $fullName = $this->titleCase($student['name']);
                [$lastName, $firstName, $middleName] = $this->splitStudentName($fullName);
                $defenseType = ['Proposal', 'Pre-Oral', 'Final'][$index % 3];
                $mode = $index % 2 === 0 ? 'Face-to-Face' : 'Online';
                $chair = $chairs[$index % count($chairs)];
                $panel = [
                    $members[$panelistOffset],
                    $members[($panelistOffset + 1) % count($members)],
                    $members[($panelistOffset + 2) % count($members)],
                ];
                $formattedRange = $scheduledAt->format('g:i A').' - '.$scheduledEnd->format('g:i A');

                DefenseRequest::withoutEvents(fn () => DefenseRequest::updateOrCreate(
                    ['school_id' => sprintf('MOCK-%d-%04d', $year, $index + 1)],
                    [
                        'submitted_by' => null,
                        'first_name' => $firstName,
                        'middle_name' => $middleName ?: null,
                        'last_name' => $lastName,
                        'program' => self::PROGRAMS[$student['program_code']][1],
                        'thesis_title' => "[MOCK] {$defenseType} defense for {$fullName}",
                        'date_of_defense' => $date->toDateString(),
                        'scheduled_date' => $scheduledAt,
                        'scheduled_time' => $slot[0],
                        'scheduled_end_time' => $slot[1],
                        'defense_duration_minutes' => 60,
                        'formatted_time_range' => $formattedRange,
                        'mode_defense' => $mode,
                        'defense_mode' => $mode,
                        'defense_venue' => $mode === 'Online' ? 'Mock Online Meeting Room' : 'Mock Graduate School Room',
                        'defense_type' => $defenseType,
                        'defense_adviser' => $student['adviser'],
                        'defense_chairperson' => $chair->name,
                        'defense_panelist1' => $panel[0]->name,
                        'defense_panelist2' => $panel[1]->name,
                        'defense_panelist3' => $panel[2]->name,
                        'status' => 'Approved',
                        'priority' => 'Medium',
                        'workflow_state' => 'scheduled',
                        'scheduling_status' => 'scheduled',
                        'adviser_status' => 'Approved',
                        'coordinator_status' => 'Approved',
                        'submitted_at' => now(),
                        'panels_assigned_at' => now(),
                        'schedule_set_at' => now(),
                        'workflow_history' => [
                            [
                                'action' => 'submitted',
                                'timestamp' => now()->toIso8601String(),
                                'user_id' => null,
                                'user_name' => 'Mock Schedule Seeder',
                                'comment' => 'Mock defense data generated from workbook student rosters.',
                                'from_state' => null,
                                'to_state' => 'submitted',
                            ],
                            [
                                'action' => 'scheduled',
                                'timestamp' => now()->toIso8601String(),
                                'user_id' => null,
                                'user_name' => 'Mock Schedule Seeder',
                                'comment' => 'Mock schedule generated relative to the current date.',
                                'from_state' => 'submitted',
                                'to_state' => 'scheduled',
                            ],
                        ],
                    ]
                ));
            }
        });

        $this->command?->info("Seeded {$studentCount} mock defense schedules using the workbook student rosters.");
    }

    private function ensureProgramsExist(array $students): void
    {
        foreach (array_unique(array_column($students, 'program_code')) as $programCode) {
            [$name, $systemCode] = self::PROGRAMS[$programCode];

            $program = ProgramRecord::firstOrNew(['program' => $systemCode]);
            if (! $program->exists) {
                $program->forceFill([
                    'name' => $name,
                    'category' => 'Masters',
                    'time_last_opened' => now()->format('H:i:s'),
                    'date_edited' => now()->toDateString(),
                ])->save();
            }
        }
    }

    private function ensureMockPanelists(): array
    {
        $chairs = collect(range(1, 3))->map(fn (int $number) => Panelist::updateOrCreate(
            ['email' => "mock-chair-{$number}@example.invalid"],
            [
                'name' => "Dr. Mock Chairperson {$number}",
                'role' => 'Chairperson',
                'status' => 'Assigned',
            ]
        ))->all();

        $members = collect(range(1, 6))->map(fn (int $number) => Panelist::updateOrCreate(
            ['email' => "mock-panelist-{$number}@example.invalid"],
            [
                'name' => "Mock Panelist {$number}",
                'role' => 'Panel Member',
                'status' => 'Assigned',
            ]
        ))->all();

        return [$chairs, $members];
    }

    private function nextWeekday(): Carbon
    {
        $date = now()->startOfDay()->addDay();

        while ($date->isWeekend()) {
            $date->addDay();
        }

        return $date;
    }

    private function titleCase(string $name): string
    {
        return mb_convert_case(trim(preg_replace('/\s+/u', ' ', $name)), MB_CASE_TITLE, 'UTF-8');
    }

    private function splitStudentName(string $name): array
    {
        [$lastName, $givenNames] = array_pad(explode(',', $name, 2), 2, '');
        $givenNameParts = preg_split('/\s+/u', trim($givenNames), -1, PREG_SPLIT_NO_EMPTY);
        $firstName = array_shift($givenNameParts) ?? 'Mock';

        return [
            trim($lastName),
            $firstName,
            implode(' ', $givenNameParts),
        ];
    }
}
