import 'package:flutter_test/flutter_test.dart';

import 'package:alerte_inondation/main.dart';

void main() {
  testWidgets("L'écran de signalement s'affiche sans erreur", (WidgetTester tester) async {
    await tester.pumpWidget(const AlerteInondationApp());
    await tester.pump();

    expect(find.text('Émettre une alerte'), findsOneWidget);
  });
}
